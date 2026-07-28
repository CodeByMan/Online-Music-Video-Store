<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);

$categories = db_fetch_all($conn, 'SELECT category_id, category_artist, category_album, category_genre, category_language, category_year FROM categories ORDER BY category_artist, category_album');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $uploaded = null;
    try {
        verify_csrf();
        $categoryId = request_positive_int($_POST, 'category_id');
        if (db_fetch_one($conn, 'SELECT category_id FROM categories WHERE category_id = ? LIMIT 1', 'i', [$categoryId]) === null) {
            throw new InvalidArgumentException('Please select a valid category.');
        }
        $uploaded = store_uploaded_file($_FILES['music_file'] ?? [], media_storage_directory('audio'), 'audio');
        db_execute(
            $conn,
            'INSERT INTO audio (audio_title, audio_name, audio_original_name, audio_mime_type, audio_size_bytes, category_id) VALUES (?, ?, ?, ?, ?, ?)',
            'ssssii',
            [$uploaded['title'], $uploaded['stored_name'], $uploaded['original_name'], $uploaded['mime_type'], $uploaded['size'], $categoryId]
        );
        redirect_with_flash('view_music.php', 'Music added successfully.');
    } catch (Throwable $exception) {
        if (is_array($uploaded)) {
            delete_file_if_safe(media_storage_directory('audio'), $uploaded['stored_name']);
        }
        error_log('[One Sound] Add music error: ' . $exception->getMessage());
        redirect_with_flash('add_music.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to add the music file. Please try again.', 'danger');
    }
}
include 'header.php';
?>
<div class="container mt-5"><form action="add_music.php" method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<div class="form-group"><label for="music_file">Music File</label><input type="file" class="form-control" id="music_file" name="music_file" accept=".mp3,audio/mpeg" required><small class="form-text text-muted">The display title is derived from the original filename; the stored filename is randomized.</small></div>
<div class="form-group"><label for="category">Select Category</label><select name="category_id" id="category" class="form-control" required><option value="">Select a Category</option><?php foreach ($categories as $row): ?><option value="<?php echo (int) $row['category_id']; ?>">Artist: <?php echo e($row['category_artist']); ?>, Album: <?php echo e($row['category_album']); ?>, Genre: <?php echo e($row['category_genre']); ?>, Language: <?php echo e($row['category_language']); ?>, Year: <?php echo (int) $row['category_year']; ?></option><?php endforeach; ?></select></div>
<button type="submit" class="btn btn-primary d-block w-100">Add Music</button></form></div>
<?php include 'footer.php'; ?>
