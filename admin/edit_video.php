<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
try {
    $videoId = request_positive_int($_GET, 'video_id');
    $video = db_fetch_one($conn, 'SELECT video_id, video_title, video_name, video_original_name, video_mime_type, video_size_bytes, category_id FROM videos WHERE video_id = ? LIMIT 1', 'i', [$videoId]);
    if ($video === null) {
        throw new RuntimeException('Video file not found.');
    }
    $categories = db_fetch_all($conn, 'SELECT category_id, category_artist, category_album, category_genre, category_language, category_year FROM categories ORDER BY category_artist, category_album');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $categoryId = request_positive_int($_POST, 'category_id');
        if (db_fetch_one($conn, 'SELECT category_id FROM categories WHERE category_id = ? LIMIT 1', 'i', [$categoryId]) === null) {
            throw new InvalidArgumentException('Please select a valid category.');
        }
        $newUpload = null;
        $values = $video;
        if (uploaded_file_present($_FILES['video_file'] ?? [])) {
            $newUpload = store_uploaded_file($_FILES['video_file'], media_storage_directory('video'), 'video');
            $values = array_merge($values, ['video_title' => $newUpload['title'], 'video_name' => $newUpload['stored_name'], 'video_original_name' => $newUpload['original_name'], 'video_mime_type' => $newUpload['mime_type'], 'video_size_bytes' => $newUpload['size']]);
        }
        try {
            db_execute($conn, 'UPDATE videos SET video_title = ?, video_name = ?, video_original_name = ?, video_mime_type = ?, video_size_bytes = ?, category_id = ? WHERE video_id = ?', 'ssssiii', [$values['video_title'], $values['video_name'], $values['video_original_name'], $values['video_mime_type'], (int) $values['video_size_bytes'], $categoryId, $videoId]);
        } catch (Throwable $exception) {
            if (is_array($newUpload)) {
                delete_file_if_safe(media_storage_directory('video'), $newUpload['stored_name']);
            }
            throw $exception;
        }
        if (is_array($newUpload)) {
            delete_file_if_safe(media_storage_directory('video'), (string) $video['video_name']);
        }
        redirect_with_flash('view_video.php', 'Video updated successfully.');
    }
} catch (Throwable $exception) {
    error_log('[One Sound] Edit video error: ' . $exception->getMessage());
    redirect_with_flash('view_video.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the video file. Please try again.', 'danger');
}
include 'header.php';
?>
<div class="container mt-5"><h2 class="text-center mb-4">Edit Video</h2><form action="edit_video.php?video_id=<?php echo (int) $videoId; ?>" method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?><div class="form-group"><label for="video_file">Video File</label><input type="file" class="form-control" id="video_file" name="video_file" accept=".mp4,video/mp4"><small class="form-text text-muted">Current title: <?php echo e($video['video_title']); ?>. Leave blank to retain the file.</small></div>
<div class="form-group"><label for="category">Select Category</label><select name="category_id" id="category" class="form-control" required><?php foreach ($categories as $category): ?><option value="<?php echo (int) $category['category_id']; ?>" <?php echo (int) $category['category_id'] === (int) $video['category_id'] ? 'selected' : ''; ?>>Artist: <?php echo e($category['category_artist']); ?>, Album: <?php echo e($category['category_album']); ?>, Genre: <?php echo e($category['category_genre']); ?>, Language: <?php echo e($category['category_language']); ?>, Year: <?php echo (int) $category['category_year']; ?></option><?php endforeach; ?></select></div>
<button type="submit" class="btn btn-primary d-block w-100">Update Video</button></form></div>
<?php include 'footer.php'; ?>
