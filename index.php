<?php

$errors = [];
$title = $author = $body = $category = $tags = '';
$imagePath = '';
$categories = ['Tech', 'Lifestyle', 'Business', 'Travel', 'Food','Other'];


$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $category = $_POST['category'] ?? '';
    $tags = trim($_POST['tags'] ?? '');


    if (!$title) $errors['title'] = 'Title is required.';
    if (!$author) $errors['author'] = 'Author name required.';
    if (strlen(strip_tags($body)) < 400) $errors['body'] = 'Minimum 400 characters required.';
    if (!$category) $errors['category'] = 'Select a category.';

    
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['image']['tmp_name']);

        if (in_array($mimeType, $allowedTypes)) {
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $errors['image'] = 'Image exceeds 2MB size limit.';
            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imgFile = uniqid('img_', true) . '.' . $ext;
                $fullPath = $uploadDir . $imgFile;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
                    if (getimagesize($fullPath) === false) {
                        unlink($fullPath);
                        $errors['image'] = 'Uploaded file is not a valid image.';
                    } else {
                        $imagePath = $fullPath;
                    }
                } else {
                    $errors['image'] = 'Upload failed.';
                }
            }
        } else {
            $errors['image'] = 'Invalid file type.';
        }
    } else {
        $errors['image'] = 'Image is required.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Blog Post</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('headerimg.png');
      background-color: #121212;
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: center;
      
    }

    .form-control,
    .form-select,
    textarea {
      background-color: #1e1e1e;
      color: #f5f5f5;
      border-color: #333;
    }

    .form-control::placeholder {
      color: #aaa;
    }

    .form-label {
      color: #f0f0f0;
    }

    .bg-white {
      background-color: #1c1c1c !important;
    }

    .btn-primary {
      background-color:rgb(57, 57, 61);
      border-color:rgb(99, 97, 103);
    }

    .btn-primary:hover {
      background-color:rgb(75, 74, 84);
      border-color: #5952d4;
    }

    textarea {
      min-height: 300px;
    }
  </style>
</head>
<body class="min-h-screen font-sans">

  <header class="text-gray-100 py-6 shadow text-center">
    <h1 class="text-4xl font-bold">Create a New Blog</h1>
  </header>

  <main class="container my-10">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)): ?>
      <div class="bg-white p-6 rounded shadow-lg">
        <img src="<?= htmlspecialchars($imagePath) ?>" class="mb-4 w-100 rounded object-cover" style="max-height: 400px;" />
        <h2 class="text-3xl font-bold mb-2"><?= htmlspecialchars($title) ?></h2>
        <p class="text-muted mb-1">By <strong><?= htmlspecialchars($author) ?></strong> in <span class="badge bg-primary"><?= htmlspecialchars($category) ?></span></p>
        <div class="text-light mt-3"><?= nl2br(htmlspecialchars_decode($body)) ?></div>
        <?php if (!empty($tags)): ?>
          <p class="mt-4 text-sm text-secondary">Tags:
            <?php foreach (explode(',', $tags) as $tag): ?>
              <span class="badge bg-info text-dark me-1">#<?= htmlspecialchars(trim($tag)) ?></span>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-light mt-4">Write another post</a>
      </div>
    <?php else: ?>
      <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-lg space-y-4">
        <div>
          <label class="form-label fw-bold">Title *</label>
          <input name="title" value="<?= htmlspecialchars($title) ?>" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" />
          <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= $errors['title'] ?></div><?php endif; ?>
        </div>

        <div>
          <label class="form-label fw-bold">Author *</label>
          <input name="author" value="<?= htmlspecialchars($author) ?>" class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" />
          <?php if (isset($errors['author'])): ?><div class="invalid-feedback"><?= $errors['author'] ?></div><?php endif; ?>
        </div>

        <div>
          <label class="form-label fw-bold">Category *</label>
          <select name="category" class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>">
            <option value="">-- Choose --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= $cat === $category ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['category'])): ?><div class="invalid-feedback"><?= $errors['category'] ?></div><?php endif; ?>
        </div>

        <div>
          <label class="form-label fw-bold">Tags *</label>
          <input name="tags" value="<?= htmlspecialchars($tags) ?>" class="form-control" />
        </div>

        <div>
          <label class="form-label fw-bold">Content *</label>
          <textarea name="body" class="form-control <?= isset($errors['body']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($body) ?></textarea>
          <?php if (isset($errors['body'])): ?><div class="invalid-feedback"><?= $errors['body'] ?></div><?php endif; ?>
        </div>

        <div>
          <label class="form-label fw-bold">Image *</label>
          <input type="file" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" />
          <?php if (isset($errors['image'])): ?><div class="invalid-feedback"><?= $errors['image'] ?></div><?php endif; ?>
        </div>
<div class="text-center">
        <button class="btn btn-primary w-40 mt-3 ">Post</button>
          </div>
      </form>
    <?php endif; ?>
  </main>

</body>
</html>
