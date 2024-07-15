<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$total = 0;

try {
    $conn = new PDO("mysql:host=localhost;dbname=toko", 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

$action = isset($_GET['action']) ? $_GET['action'] : "";

if ($action == 'addcart' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = "SELECT * FROM products WHERE sku=:sku";
    $stmt = $conn->prepare($query);
    $stmt->bindParam('sku', $_POST['sku']);
    $stmt->execute();
    $product = $stmt->fetch();

    if ($product) {
        $currentQty = isset($_SESSION['products'][$_POST['sku']]) ? $_SESSION['products'][$_POST['sku']]['qty'] + 1 : 1;
        $_SESSION['products'][$_POST['sku']] = array(
            'qty' => $currentQty,
            'name' => $product['name'],
            'image' => $product['image'],
            'price' => $product['price']
        );
    } else {
        echo "Product not found.";
    }
    header("Location:index.php");
    exit();
}

if ($action == 'emptyall') {
    $_SESSION['products'] = array();
    header("Location:index.php");
    exit();
}

if ($action == 'empty') {
    $sku = $_GET['sku'];
    $products = $_SESSION['products'];
    unset($products[$sku]);
    $_SESSION['products'] = $products;
    header("Location:index.php");
    exit();
}

$query = "SELECT * FROM products";
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll();

if (empty($products)) {
    echo "No products found in the database.";
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHP Shopping Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <nav class="navbar navbar-dark bg-success mb-3">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">Shopping Cart</span>
      <div class="d-flex">
        <?php if (isset($_SESSION['user'])): ?>
          <a href="logout.php" class="btn btn-danger me-2">Logout</a>
        <?php else: ?>
          <a href="login.php" class="btn btn-primary me-2">Login</a>
          <a href="register.php" class="btn btn-secondary">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
  
  <?php if (!empty($_SESSION['products'])): ?>
  <nav class="navbar navbar-dark bg-success mb-3">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">Shopping Cart</span>
      <a href="index.php?action=emptyall" class="btn btn-danger">Empty cart</a>
    </div>
  </nav>
  <table class="table table-hover">
    <thead class="table-success">
      <tr>
        <th scope="col">Image</th>
        <th scope="col">Name</th>
        <th scope="col">Price</th>
        <th scope="col">Qty</th>
        <th scope="col">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($_SESSION['products'] as $key => $product): ?>
      <tr>
        <td><img src="<?php echo $product['image'] ?>" width="50px"></td>
        <td><?php echo $product['name'] ?></td>
        <td>$<?php echo $product['price'] ?></td>
        <td><?php echo $product['qty'] ?></td>
        <td><a href="index.php?action=empty&sku=<?php echo $key ?>" class="btn btn-danger">Delete</a></td>
      </tr>
      <?php $total += $product['price'] * $product['qty']; ?>
      <?php endforeach; ?>
      <tr>
        <td colspan="5" class="text-end"><h4>Total: $<?php echo $total ?></h4></td>
      </tr>
    </tbody>
  </table>
  <?php endif; ?>
  <nav class="navbar navbar-dark bg-success mb-3">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">Products</span>
    </div>
  </nav>
  <div class="row">
    <?php foreach ($products as $product): ?>
    <div class="col-md-4 mb-4">
      <div class="card">
        <img src="<?php echo $product['image'] ?>" class="card-img-top" alt="Product Image">
        <div class="card-body text-center">
          <h5 class="card-title"><?php echo $product['name'] ?></h5>
          <p class="card-text text-success"><b>$<?php echo $product['price'] ?></b></p>
          <form method="post" action="index.php?action=addcart">
            <input type="hidden" name="sku" value="<?php echo $product['sku'] ?>">
            <button type="submit" class="btn btn-warning">Add To Cart</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
