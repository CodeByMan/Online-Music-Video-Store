<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
try {
    $categoryId = request_positive_int($_GET, 'category_id');
    $category = db_fetch_one($conn, 'SELECT * FROM categories WHERE category_id = ? LIMIT 1', 'i', [$categoryId]);
    if ($category === null) {
        throw new RuntimeException('Category not found.');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $artist = input_text($_POST, 'artist', 1, 100);
        $album = input_text($_POST, 'album', 1, 150);
        $genre = input_text($_POST, 'genre', 1, 100);
        $language = input_text($_POST, 'language', 1, 100);
        $year = input_year($_POST, 'year');
        $image = (string) $category['category_image'];
        $newImage = null;
        if (uploaded_file_present($_FILES['category_image'] ?? [])) {
            $upload = store_uploaded_file($_FILES['category_image'], __DIR__ . '/uploads/images', 'image');
            $newImage = $upload['stored_name'];
            $image = $newImage;
        }
        try {
            db_execute($conn, 'UPDATE categories SET category_artist = ?, category_album = ?, category_genre = ?, category_language = ?, category_year = ?, category_image = ? WHERE category_id = ?', 'ssssisi', [$artist, $album, $genre, $language, $year, $image, $categoryId]);
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                delete_file_if_safe(__DIR__ . '/uploads/images', $newImage);
            }
            throw $exception;
        }
        if ($newImage !== null && db_fetch_one($conn, 'SELECT category_id FROM categories WHERE category_image = ? LIMIT 1', 's', [$category['category_image']]) === null) {
            delete_file_if_safe(__DIR__ . '/uploads/images', (string) $category['category_image']);
        }
        redirect_with_flash('view_category.php', 'Category updated successfully.');
    }
} catch (Throwable $exception) {
    error_log('[One Sound] Edit category error: ' . $exception->getMessage());
    redirect_with_flash('view_category.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the category. Please try again.', 'danger');
}
include 'header.php';
?>
<div class="container mt-5"><form action="edit_category.php?category_id=<?php echo (int) $categoryId; ?>" method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<div class="form-row"><div class="form-group col-md-12"><label for="artist">Artist</label><input id="artist" type="text" class="form-control" name="artist" value="<?php echo e($category['category_artist']); ?>" maxlength="100" required></div></div>
<div class="form-row"><div class="form-group col-md-6"><label for="album">Album</label><input id="album" type="text" class="form-control" name="album" value="<?php echo e($category['category_album']); ?>" maxlength="150" required></div><div class="form-group col-md-6"><label for="genre">Genre</label><input id="genre" type="text" class="form-control" name="genre" value="<?php echo e($category['category_genre']); ?>" maxlength="100" required></div></div>
<div class="form-row"><div class="form-group col-md-6"><label for="language">Language</label><input id="language" type="text" class="form-control" name="language" value="<?php echo e($category['category_language']); ?>" maxlength="100" required></div><div class="form-group col-md-6"><label for="year">Year</label><input id="year" type="number" class="form-control" name="year" value="<?php echo (int) $category['category_year']; ?>" min="1900" max="<?php echo (int) date('Y') + 1; ?>" required></div></div>
<div class="form-row"><div class="form-group col-md-12"><p class="mb-1">Current Image</p><img src="uploads/images/<?php echo e($category['category_image']); ?>" alt="Current category cover" width="200"><br><br><label for="category_image">Category Image</label><input id="category_image" type="file" class="form-control" name="category_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div></div>
<button type="submit" class="btn btn-primary d-block w-100">Update Category</button></form></div>
<?php include 'footer.php'; ?>
