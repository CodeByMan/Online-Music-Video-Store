<div class="bradcam_area breadcam_bg_2">
    <div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3><?php echo e($pageTitle); ?></h3></div></div></div></div>
</div>
<div class="container mt-5">
    <h4 class="text-center">Categories:</h4>
    <div class="row mb-4"><div class="col-12 text-center">
        <a href="music.php" class="btn btn-danger mx-2 px-3">All</a><a href="artist.php" class="btn btn-warning mx-2 px-3">Artist</a><a href="album.php" class="btn btn-success mx-2 px-3">Album</a><a href="genre.php" class="btn btn-primary mx-2 px-3">Genre</a><a href="language.php" class="btn btn-info mx-2 px-3">Language</a><a href="year.php" class="btn btn-secondary mx-2 px-3">Year</a>
    </div></div>
    <div class="row mb-4"><div class="col-12 text-center"><form method="GET" action="search_audio.php" class="d-flex justify-content-center"><div class="input-group" style="max-width: 400px;"><input type="search" name="q" class="form-control form-control-sm" placeholder="Search" aria-label="Search music"><button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button></div></form></div></div>
    <hr class="mb-4">
    <?php if ($groupedSongs !== []): foreach ($groupedSongs as $groupName => $songs): ?>
        <h3 class="mb-3 text-center"><?php echo e($groupName); ?></h3>
        <div class="table-responsive"><table class="table table-bordered" style="table-layout: fixed;"><thead class="bg-danger text-white"><tr><th>Song</th></tr></thead><tbody>
        <?php foreach ($songs as $song): ?><tr><td style="overflow-wrap: anywhere;"><a class="text-danger" href="detail.php?audio_id=<?php echo (int) $song['audio_id']; ?>"><?php echo e($song['audio_title']); ?> (Click Here)</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endforeach; else: ?><p><?php echo e($emptyText); ?></p><?php endif; ?>
    <?php echo pagination_links($pagination['page'], $totalPages); ?>
</div>
