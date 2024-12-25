<?php
require_once('includes/load.php');



/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
    global $db;
    return find_by_sql("SELECT * FROM " . $table);
}

/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql, $params = [])
{
    global $db;
    $stmt = $db->query($sql, $params);
    return $db->fetch_array($stmt);
}

/*--------------------------------------------------------------*/
/* Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table, $id)
{
    global $db;
    $sql = "SELECT * FROM $table WHERE id = :id LIMIT 1";
    $params = [':id' => $id];
    $stmt = $db->query($sql, $params);
    return $db->fetch_assoc($stmt);
}

/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table, $id)
{
    global $db;
    $sql = "DELETE FROM $table WHERE id = :id";
    $params = [':id' => $id];
    $stmt = $db->query($sql, $params);
    return ($db->affected_rows($stmt) === 1);
}

/*--------------------------------------------------------------*/
/* Function for Count id By table name
/*--------------------------------------------------------------*/
function count_by_id($table)
{
    global $db;
    $sql = "SELECT COUNT(id) AS total FROM $table";
    $stmt = $db->query($sql);
    return $db->fetch_assoc($stmt);
}

/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table)
{
    global $db;
    $sql = "SELECT to_regclass(:table) AS exists";
    $params = [':table' => $table];
    $stmt = $db->query($sql, $params);
    $result = $db->fetch_assoc($stmt);
    return ($result['exists'] !== null);
}
/*--------------------------------------------------------------*/
/* aunthicate
/*--------------------------------------------------------------*/
function authenticate($username = '', $password = '') {
  global $db;

  $sql = "SELECT id, password FROM users WHERE username = :username LIMIT 1";
  $params = [':username' => $username];
  $result = $db->query($sql, $params);

  if ($db->num_rows($result)) {
      $user = $db->fetch_assoc($result);
      if (password_verify($password, $user['password'])) {
          return $user['id'];
      }
  }
  return false;
}

/*--------------------------------------------------------------*/
/* aunthicatev2
/*--------------------------------------------------------------*/
function authenticate_v2($username = '', $password = '') {
  global $db;

  $sql = "SELECT id, username, password, user_level FROM users WHERE username = :username LIMIT 1";
  $params = [':username' => $username];
  $result = $db->query($sql, $params);

  if ($db->num_rows($result)) {
      $user = $db->fetch_assoc($result);
      if (password_verify($password, $user['password'])) {
          return $user;
      }
  }
  return false;
}

/*--------------------------------------------------------------*/
/* current user
/*--------------------------------------------------------------*/
function current_user() {
  static $current_user;
  global $db;

  if (!$current_user) {
      if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
          $user_id = intval($_SESSION['user_id']);
          $current_user = find_by_id('users', $user_id);
      }
  }
  return $current_user;
}
/*--------------------------------------------------------------*/
/* update last login
/*--------------------------------------------------------------*/

function updateLastLogIn($user_id) {
  global $db;
  $date = make_date();
  $sql = "UPDATE users SET last_login = :date WHERE id = :user_id";
  $params = [':date' => $date, ':user_id' => $user_id];
  $result = $db->query($sql, $params);

  return ($result && $db->affected_rows($result) === 1);
}
/*--------------------------------------------------------------*/
/* page_require_level
/*--------------------------------------------------------------*/
function page_require_level($require_level) {
  global $session;
  $current_user = current_user();

  if (!$current_user) {
      $session->msg('d', 'Please log in...');
      redirect('index.php', false);
  }

  $login_level = find_by_groupLevel($current_user['user_level']);
  if ($login_level['group_status'] === 0) { // Assuming group_status is stored as an integer
      $session->msg('d', 'This level user has been banned!');
      redirect('home.php', false);
  }

  if ($current_user['user_level'] <= (int)$require_level) {
      return true;
  }

  $session->msg("d", "Sorry! You don't have permission to view the page.");
  redirect('home.php', false);
}


function find_product_by_title($product_name){
  global $db;
  $p_name = remove_junk($db->escape($product_name));
  $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
  $result = find_by_sql($sql);
  return $result;
}

/*--------------------------------------------------------------*/
/* Function for Finding all product info by product title
/* Request coming from ajax.php
/*--------------------------------------------------------------*/
function find_all_product_info_by_title($title){
 global $db;
 $sql  = "SELECT * FROM products ";
 $sql .= " WHERE name ='{$title}'";
 $sql .=" LIMIT 1";
 return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Function for Update product quantity
/*--------------------------------------------------------------*/
function update_product_qty($qty,$p_id){
 global $db;
 $qty = (int) $qty;
 $id  = (int)$p_id;
 $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
 $result = $db->query($sql);
 return($db->affected_rows() === 1 ? true : false);

}
/*--------------------------------------------------------------*/
/* Function for Display Recent product Added
/*--------------------------------------------------------------*/
function find_recent_product_added($limit){
global $db;
$sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
$sql  .= "m.file_name AS image FROM products p";
$sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
$sql  .= " LEFT JOIN media m ON m.id = p.media_id";
$sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Find Highest saleing Product
/*--------------------------------------------------------------*/
function find_higest_saleing_product($limit){
global $db;
$sql  = "SELECT p.name, COUNT(s.product_id) AS totalSold, SUM(s.qty) AS totalQty";
$sql .= " FROM sales s";
$sql .= " LEFT JOIN products p ON p.id = s.product_id ";
$sql .= " GROUP BY s.product_id";
$sql .= " ORDER BY SUM(s.qty) DESC LIMIT ".$db->escape((int)$limit);
return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for find all sales
/*--------------------------------------------------------------*/
function find_all_sale(){
global $db;
$sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
$sql .= " FROM sales s";
$sql .= " LEFT JOIN products p ON s.product_id = p.id";
$sql .= " ORDER BY s.date DESC";
return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Display Recent sale
/*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
global $db;
$sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
$sql .= " FROM sales s";
$sql .= " LEFT JOIN products p ON s.product_id = p.id";
$sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date,$end_date){
global $db;
$start_date  = date("Y-m-d", strtotime($start_date));
$end_date    = date("Y-m-d", strtotime($end_date));
$sql  = "SELECT s.date, p.name,p.sale_price,p.buy_price,";
$sql .= "COUNT(s.product_id) AS total_records,";
$sql .= "SUM(s.qty) AS total_sales,";
$sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price,";
$sql .= "SUM(p.buy_price * s.qty) AS total_buying_price ";
$sql .= "FROM sales s ";
$sql .= "LEFT JOIN products p ON s.product_id = p.id";
$sql .= " WHERE s.date BETWEEN '{$start_date}' AND '{$end_date}'";
$sql .= " GROUP BY DATE(s.date),p.name";
$sql .= " ORDER BY DATE(s.date) DESC";
return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function  dailySales($year,$month){
global $db;
$sql  = "SELECT s.qty,";
$sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
$sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
$sql .= " FROM sales s";
$sql .= " LEFT JOIN products p ON s.product_id = p.id";
$sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m' ) = '{$year}-{$month}'";
$sql .= " GROUP BY DATE_FORMAT( s.date,  '%e' ),s.product_id";
return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function  monthlySales($year){
global $db;
$sql  = "SELECT s.qty,";
$sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
$sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
$sql .= " FROM sales s";
$sql .= " LEFT JOIN products p ON s.product_id = p.id";
$sql .= " WHERE DATE_FORMAT(s.date, '%Y' ) = '{$year}'";
$sql .= " GROUP BY DATE_FORMAT( s.date,  '%c' ),s.product_id";
$sql .= " ORDER BY date_format(s.date, '%c' ) ASC";
return find_by_sql($sql);
}
if (!function_exists('current_user')) {
  function current_user() {
      static $current_user;
      global $db;

      if (!$current_user) {
          // Check if the session contains a valid user ID
          if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
              $user_id = intval($_SESSION['user_id']);
              $current_user = find_by_id('users', $user_id);

              // Check if user exists in the database
              if (!$current_user) {
                  // User ID is invalid, clear the session
                  unset($_SESSION['user_id']);
                  return null;
              }
          } else {
              // No user ID in the session
              return null;
          }
      }
      return $current_user;
  }
}


?>
