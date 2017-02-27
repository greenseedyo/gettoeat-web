<?php
require_once 'config.php';

if ($_POST) {
    if ('add_category' == $_POST['form_name']) {
        $category = $store->create_categories($_POST);
        header('Location: manage_items.php?category_id=' . $category->id);
    } elseif ('edit_category' == $_POST['form_name']) {
        $category = $store->getCategoryById($_POST['id']);
        if (!$category instanceof CategoryRow) {
            die('bad session.');
        }
        $category->update($_POST);
        header('Location: manage_items.php?category_id=' . $category->id);
    } elseif ('products_order' == $_POST['form_name']) {
        $data = json_decode($_POST['data'], true);
        foreach ($data['on'] as $id => $position) {
            $product_data = array(
                'off' => 0,
                'position' => $position + 1,
            );
            $store->getProductById($id)->update($product_data);
        }
        foreach ($data['off'] as $id => $position) {
            $product_data = array(
                'off' => 1,
                'position' => 0,
            );
            $store->getProductById($id)->update($product_data);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
    } elseif ('add_product' == $_POST['form_name']) {
        $category = $store->getCategoryById($_GET['category_id']);
        $category->create_products($_POST);
        header('Location: manage_items.php?category_id=' . $_GET['category_id']);
    } elseif ('edit_product' == $_POST['form_name']) {
        // TODO: use $store->getProductById()
        $product = $store->getProductById($_POST['id']);
        $product->update($_POST);
        header('Location: manage_items.php?category_id=' . $product->category_id);
    }
}

if ($_GET['category_id']) {
    $category = $store->getCategoryById($_GET['category_id']);
    $products = $category->products->order('position ASC');
    $online_products = array();
    $offline_products = array();
    foreach ($products as $product) {
        if ($product->off) {
            $offline_products[] = $product;
        } else {
            $online_products[] = $product;
        }
    }
} elseif ($_GET['product']) {
    $product = $store->getProductById($_GET['product']);
} else {
    $categories = $store->categories;
}

include(VIEWS_DIR . '/manage_items.html');
