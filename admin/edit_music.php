<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
try {
    $audioId = request_positive_int($_GET, 'audio_id');
    $audio = db_fetch_one($conn, 'SELECT audio_id, audio_title, audio_name, audio_original_name, audio_mime_type, audio_size_bytes, category_id FROM audio WHERE audio_id = ? LIMIT 1', 'i', [$audioId]);
    if ($audio === null) {
        throw new RuntimeException('Music file not found.');
    }
    $categories = db_fetch_all($conn, 'SELECT category_id, category_artist, category_album, category_genre, category_language, category_year FROM categories ORDER BY category_artist, category_album');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $categoryId = request_positive_int($_POST, 'category_id');
        if (db_fetch_one($conn, 'SELECT category_id FROM categories WHERE category_id = ? LIMIT 1', 'i', [$categoryId]) === null) {
            throw new InvalidArgumentException('Please select a valid category.');
        }

        $newUpload = null;
        $values = $audio;
        if (uploaded_file_present($_FILES['music_file'] ?? [])) {
            $newUpload = store_uploaded_file($_FILES['music_file'], media_storage_directory('audio'), 'audio');
            $values = array_merge($values, [
                'audio_title' => $newUpload['title'],
                'audio_name' => $newUpload['stored_name'],
                'audio_original_name' => $newUpload['original_name'],
                'audio_mime_type' => $newUpload['mime_type'],
                'audio_size_bytes' => $newUpload['size'],
            ]);
        }

        try {
            db_execute($conn, 'UPDATE audio SET audio_title = ?, audio_name = ?, audio_original_name = ?, audio_mime_type = ?, audio_size_bytes = ?, category_id = ? WHERE audio_id = ?', 'ssssiii', [$values['audio_title'], $values['audio_name'], $values['audio_original_name'], $values['audio_mime_type'], (int) $values['audio_size_bytes'], $categoryId, $audioId]);
        } catch (Throwable $exception) {
            if (is_array($newUpload)) {
                delete_file_if_safe(media_storage_directory('audio'), $newUpload['stored_name']);
            }
            throw $exception;
        }

        if (is_array($newUpload)) {
            delete_file_if_safe(media_storage_directory('audio'), (string) $audio['audio_name']);
        }
        redirect_with_flash('view_music.php', 'Music updated successfully.');
    }
} catch (Throwable $exception) {
    error_log('[One Sound] Edit music error: ' . $exception->getMessage());
    redirect_with_flash('view_music.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the music file. Please try again.', 'danger');
}
include 'header.php';
?>
<div class="container mt-5"><form action="edit_music.php?audio_id=<?php echo (int) $audioId; ?>" method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<div class="form-group"><label for="music_file">Music File</label><input type="file" class="form-control" id="music_file" name="music_file" accept=".mp3,audio/mpeg"><small>Current title: <?php echo e($audio['audio_title']); ?>. Leave blank to retain the file.</small></div>
<div class="form-group"><label for="category">Select Category</label><select name="category_id" id="category" class="form-control" required><?php foreach ($categories as $category): ?><option value="<?php echo (int) $category['category_id']; ?>" <?php echo (int) $audio['category_id'] === (int) $category['category_id'] ? 'selected' : ''; ?>>Artist: <?php echo e($category['category_artist']); ?>, Album: <?php echo e($category['category_album']); ?>, Genre: <?php echo e($category['category_genre']); ?>, Language: <?php echo e($category['category_language']); ?>, Year: <?php echo (int) $category['category_year']; ?></option><?php endforeach; ?></select></div>
<button type="submit" class="btn btn-primary d-block w-100">Update Music</button></form></div>
<?php include 'footer.php'; ?>
