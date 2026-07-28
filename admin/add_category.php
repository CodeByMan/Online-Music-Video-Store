<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $uploadedImage = null;
    try {
        verify_csrf();
        $artist = input_text($_POST, 'artist', 1, 100);
        $album = input_text($_POST, 'album', 1, 150);
        $genre = input_text($_POST, 'genre', 1, 100);
        $language = input_text($_POST, 'language', 1, 100);
        $year = input_year($_POST, 'year');
        $upload = store_uploaded_file($_FILES['category_image'] ?? [], __DIR__ . '/uploads/images', 'image');
        $uploadedImage = $upload['stored_name'];
        db_execute($conn, 'INSERT INTO categories (category_artist, category_album, category_genre, category_language, category_year, category_image) VALUES (?, ?, ?, ?, ?, ?)', 'ssssis', [$artist, $album, $genre, $language, $year, $uploadedImage]);
        redirect_with_flash('view_category.php', 'Category added successfully.');
    } catch (Throwable $exception) {
        if ($uploadedImage !== null) {
            delete_file_if_safe(__DIR__ . '/uploads/images', $uploadedImage);
        }
        error_log('[One Sound] Add category error: ' . $exception->getMessage());
        redirect_with_flash('add_category.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to add the category. Please try again.', 'danger');
    }
}
include 'header.php';
?>
<div class="container mt-5"><form action="add_category.php" method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<div class="form-row"><div class="form-group col-md-12"><label for="artist">Artist</label><input id="artist" type="text" class="form-control" name="artist" maxlength="100" required></div></div>
<div class="form-row"><div class="form-group col-md-6"><label for="album">Album</label><input id="album" type="text" class="form-control" name="album" maxlength="150" required></div><div class="form-group col-md-6"><label for="genre">Genre</label><input id="genre" type="text" class="form-control" name="genre" maxlength="100" required></div></div>
<div class="form-row"><div class="form-group col-md-6"><label for="language">Language</label><input id="language" type="text" class="form-control" name="language" maxlength="100" required></div><div class="form-group col-md-6"><label for="year">Year</label><input id="year" type="number" class="form-control" name="year" min="1900" max="<?php echo (int) date('Y') + 1; ?>" required></div></div>
<div class="form-row"><div class="form-group col-md-12"><label for="category_image">Category Image</label><input id="category_image" type="file" class="form-control" name="category_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required></div></div>
<button type="submit" class="btn btn-primary d-block w-100">Add Category</button></form></div>
<?php include 'footer.php'; ?>
