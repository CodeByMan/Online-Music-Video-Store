/*
 * One Sound audio player compatibility layer.
 * Preserves the original One Sound audioplayer UI while supporting
 * secured media.php URLs that do not end with a file extension.
 */
(function ($, window, document) {
    'use strict';

    if (!$ || !$.fn || !window.HTMLAudioElement) {
        return;
    }

    var clamp = function (value, minimum, maximum) {
        return Math.min(maximum, Math.max(minimum, value));
    };

    var formatTime = function (seconds) {
        if (!Number.isFinite(seconds) || seconds < 0) {
            return '00:00';
        }

        var totalSeconds = Math.floor(seconds);
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var remainder = totalSeconds % 60;

        var minuteText = String(minutes).padStart(2, '0');
        var secondText = String(remainder).padStart(2, '0');

        return hours > 0
            ? String(hours).padStart(2, '0') + ':' + minuteText + ':' + secondText
            : minuteText + ':' + secondText;
    };

    var percentageFromPointer = function (event, element) {
        var originalEvent = event.originalEvent || event;
        var pointer = originalEvent.touches && originalEvent.touches.length
            ? originalEvent.touches[0]
            : originalEvent.changedTouches && originalEvent.changedTouches.length
                ? originalEvent.changedTouches[0]
                : originalEvent;

        var rectangle = element.getBoundingClientRect();

        if (!pointer || rectangle.width <= 0) {
            return 0;
        }

        return clamp((pointer.clientX - rectangle.left) / rectangle.width, 0, 1);
    };

    $.fn.audioPlayer = function (options) {
        var settings = $.extend({
            classPrefix: 'audioplayer',
            strPlay: 'Play',
            strPause: 'Pause',
            strVolume: 'Mute or unmute'
        }, options || {});

        return this.each(function () {
            var audio = this;
            var $audio = $(audio);

            if (
                !audio ||
                audio.tagName.toLowerCase() !== 'audio' ||
                $audio.data('oneSoundAudioPlayer')
            ) {
                return;
            }

            $audio.data('oneSoundAudioPlayer', true);

            var prefix = settings.classPrefix;
            var $player = $('<div>', { 'class': prefix });
            var $playPause = $('<div>', {
                'class': prefix + '-playpause',
                'title': settings.strPlay,
                'role': 'button',
                'tabindex': '0',
                'aria-label': settings.strPlay
            }).append($('<a>', { 'href': '#', 'aria-hidden': 'true' }));
            var $currentTime = $('<div>', {
                'class': prefix + '-time ' + prefix + '-time-current'
            }).text('00:00');
            var $bar = $('<div>', {
                'class': prefix + '-bar',
                'role': 'slider',
                'tabindex': '0',
                'aria-label': 'Audio progress',
                'aria-valuemin': '0',
                'aria-valuemax': '100',
                'aria-valuenow': '0'
            });
            var $barLoaded = $('<div>', { 'class': prefix + '-bar-loaded' });
            var $barPlayed = $('<div>', { 'class': prefix + '-bar-played' });
            var $duration = $('<div>', {
                'class': prefix + '-time ' + prefix + '-time-duration'
            }).html('&hellip;');
            var $volume = $('<div>', { 'class': prefix + '-volume' });
            var $volumeButton = $('<div>', {
                'class': prefix + '-volume-button',
                'title': settings.strVolume,
                'role': 'button',
                'tabindex': '0',
                'aria-label': settings.strVolume,
                'aria-pressed': 'false'
            }).append($('<a>', { 'href': '#', 'aria-hidden': 'true' }));
            var $volumeAdjust = $('<div>', {
                'class': prefix + '-volume-adjust',
                'role': 'slider',
                'tabindex': '0',
                'aria-label': 'Volume',
                'aria-valuemin': '0',
                'aria-valuemax': '100',
                'aria-valuenow': String(Math.round(audio.volume * 100))
            });
            var $volumeTrack = $('<div>').append($('<div>'));

            $bar.append($barLoaded, $barPlayed);
            $volumeAdjust.append($volumeTrack);
            $volume.append($volumeButton, $volumeAdjust);

            $audio.before($player);
            $player.append(
                $audio,
                $playPause,
                $currentTime,
                $bar,
                $duration,
                $volume
            );

            audio.controls = false;
            audio.preload = audio.preload || 'metadata';

            $audio.css({
                width: 0,
                height: 0,
                visibility: 'hidden',
                position: 'absolute',
                left: '-9999px'
            });

            var previousVolume = audio.volume > 0 ? audio.volume : 1;
            var draggingProgress = false;
            var draggingVolume = false;

            var setPlayingState = function (playing) {
                $player.toggleClass(prefix + '-playing', playing);
                $playPause
                    .attr('title', playing ? settings.strPause : settings.strPlay)
                    .attr('aria-label', playing ? settings.strPause : settings.strPlay);
            };

            var updateDuration = function () {
                $duration.text(formatTime(audio.duration));
            };

            var updateProgress = function () {
                var durationValue = Number.isFinite(audio.duration) && audio.duration > 0
                    ? audio.duration
                    : 0;
                var percentage = durationValue > 0
                    ? clamp((audio.currentTime / durationValue) * 100, 0, 100)
                    : 0;

                $currentTime.text(formatTime(audio.currentTime));
                $barPlayed.css('width', percentage + '%');
                $bar.attr('aria-valuenow', String(Math.round(percentage)));
            };

            var updateBuffered = function () {
                if (!Number.isFinite(audio.duration) || audio.duration <= 0 || audio.buffered.length === 0) {
                    $barLoaded.css('width', '0%');
                    return;
                }

                try {
                    var bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
                    var percentage = clamp((bufferedEnd / audio.duration) * 100, 0, 100);
                    $barLoaded.css('width', percentage + '%');
                } catch (error) {
                    $barLoaded.css('width', '0%');
                }
            };

            var updateVolume = function () {
                var volumePercentage = Math.round(audio.volume * 100);
                var muted = audio.muted || audio.volume === 0;

                $player.toggleClass(prefix + '-mute', muted);
                $volumeTrack.children('div').css('width', volumePercentage + '%');
                $volumeAdjust.attr('aria-valuenow', String(volumePercentage));
                $volumeButton.attr('aria-pressed', muted ? 'true' : 'false');
            };

            var togglePlayback = function () {
                if (audio.paused || audio.ended) {
                    $('audio').not(audio).each(function () {
                        this.pause();
                    });

                    var playPromise = audio.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {
                            setPlayingState(false);
                        });
                    }
                } else {
                    audio.pause();
                }
            };

            var seekFromEvent = function (event) {
                if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
                    return;
                }

                audio.currentTime = percentageFromPointer(event, $bar.get(0)) * audio.duration;
                updateProgress();
            };

            var setVolumeFromEvent = function (event) {
                var nextVolume = percentageFromPointer(event, $volumeAdjust.get(0));
                audio.muted = false;
                audio.volume = nextVolume;

                if (nextVolume > 0) {
                    previousVolume = nextVolume;
                }
            };

            $playPause.on('click', function (event) {
                event.preventDefault();
                togglePlayback();
            }).on('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    togglePlayback();
                }
            });

            $bar.on('mousedown touchstart', function (event) {
                draggingProgress = true;
                seekFromEvent(event);
                event.preventDefault();
            }).on('click', function (event) {
                seekFromEvent(event);
            }).on('keydown', function (event) {
                if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
                    return;
                }

                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                    event.preventDefault();
                    var direction = event.key === 'ArrowRight' ? 5 : -5;
                    audio.currentTime = clamp(audio.currentTime + direction, 0, audio.duration);
                    updateProgress();
                }
            });

            $volumeButton.on('click', function (event) {
                event.preventDefault();

                if (audio.muted || audio.volume === 0) {
                    audio.muted = false;
                    audio.volume = previousVolume > 0 ? previousVolume : 1;
                } else {
                    previousVolume = audio.volume;
                    audio.muted = true;
                }
            }).on('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $volumeButton.trigger('click');
                }
            });

            $volumeAdjust.on('mousedown touchstart', function (event) {
                draggingVolume = true;
                setVolumeFromEvent(event);
                event.preventDefault();
            }).on('click', function (event) {
                setVolumeFromEvent(event);
            }).on('keydown', function (event) {
                if (
                    event.key === 'ArrowDown' ||
                    event.key === 'ArrowLeft' ||
                    event.key === 'ArrowUp' ||
                    event.key === 'ArrowRight'
                ) {
                    event.preventDefault();
                    var change = event.key === 'ArrowUp' || event.key === 'ArrowRight' ? 0.05 : -0.05;
                    audio.muted = false;
                    audio.volume = clamp(audio.volume + change, 0, 1);

                    if (audio.volume > 0) {
                        previousVolume = audio.volume;
                    }
                }
            });

            $(document).on('mousemove.oneSoundAudio touchmove.oneSoundAudio', function (event) {
                if (draggingProgress) {
                    seekFromEvent(event);
                }

                if (draggingVolume) {
                    setVolumeFromEvent(event);
                }
            }).on('mouseup.oneSoundAudio touchend.oneSoundAudio touchcancel.oneSoundAudio', function () {
                draggingProgress = false;
                draggingVolume = false;
            });

            audio.addEventListener('play', function () {
                setPlayingState(true);
            });
            audio.addEventListener('pause', function () {
                setPlayingState(false);
            });
            audio.addEventListener('ended', function () {
                setPlayingState(false);
                updateProgress();
            });
            audio.addEventListener('loadedmetadata', function () {
                updateDuration();
                updateProgress();
                updateBuffered();
            });
            audio.addEventListener('durationchange', updateDuration);
            audio.addEventListener('timeupdate', updateProgress);
            audio.addEventListener('progress', updateBuffered);
            audio.addEventListener('volumechange', updateVolume);
            audio.addEventListener('canplay', updateBuffered);
            audio.addEventListener('error', function () {
                setPlayingState(false);
                $player.addClass(prefix + '-error');
            });

            updateDuration();
            updateProgress();
            updateBuffered();
            updateVolume();

            if (audio.autoplay) {
                var autoplayPromise = audio.play();
                if (autoplayPromise && typeof autoplayPromise.catch === 'function') {
                    autoplayPromise.catch(function () {
                        setPlayingState(false);
                    });
                }
            }
        });
    };

    $(function () {
        $('audio').audioPlayer();
    });
}(window.jQuery, window, document));
