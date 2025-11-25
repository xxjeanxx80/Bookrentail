<?php
require('topNav.php');

// ============================================================================
// CATEGORIES MANAGEMENT - CLEAN CODE GIẢI THÍCH TẠI SAU
// ============================================================================

// Xử lý category status update từ URL
// Giải thích: Admin có thể toggle Active/Inactive status cho category
// NGHIỆP VỤ: Ẩn/hiện category trên frontend mà không cần xóa
if (isset($_GET['type']) && $_GET['type'] != ' ') {
  $type = getSafeValue($con, $_GET['type']);
  
  if ($type == 'status') {
    // Toggle status active/deactive
    // LOGIC: active = 1 (hiển thị), deactive = 0 (ẩn)
    $operation = getSafeValue($con, $_GET['operation']);
    $id = getSafeValue($con, $_GET['id']);
    if ($operation == 'active') {
      $status = '1';
    } else {
      $status = '0';
    }
    $updateStatusSql = "update categories set status='$status' where id='$id'";
    pg_query($con, $updateStatusSql);
  }

  if ($type == 'delete') {
    // Xóa category khỏi database
    // NGHIỆP VỤ: Xóa vĩnh viễn category
    $id = getSafeValue($con, $_GET['id']);
    $deleteSql = "delete from categories where id='$id'";
    pg_query($con, $deleteSql);
  }
}

// Lấy tất cả categories từ database
// Giải thích: SELECT tất cả categories sắp xếp theo tên alphabet
$sql = "select * from categories order by category asc";
$res = pg_query($con, $sql);

?>

<!--Main layout-->
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Categories</h4>
        <hr>
        <br>
    </div>
    <h5 class="btn btn-white ms-5 px-2 py-1 fs-6 "><a class="link-dark" href="manageCategories.php">Add
            Categories</a></h5>
    <div class="">
        <table class="table">
            <thead>
                <tr>
                    <th>Categories</th>
                    <th>Status</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
        // Hiển thị danh sách categories
        // Giải thích: Loop qua từng category và hiển thị với status toggle buttons
        while ($row = pg_fetch_assoc($res)) { ?>
                <tr>
                    <td> <?php echo $row['category'] ?> </td>
                    <td>
                        <?php
                        // Hiển thị status button (Active/Inactive)
                        // LOGIC: Nếu status = 1 -> Active button màu xanh, nếu không -> Inactive button màu vàng
              if ($row['status'] == 1) {
                echo "<a class='link-white btn btn-success px-2 py-1' href='?type=status&operation=deactive&id=" . $row['id'] .
                  "'>Active</a>&nbsp&nbsp";
              } else {
                echo "<a class='link-white btn btn-warning px-2 py-1' href='?type=status&operation=active&id=" . $row['id'] .
                  "'>Inactive</a>&nbsp&nbsp";
              }
              ?>
                    </td>
                    <td> <?php echo "<a class='link-white btn btn-primary px-2 py-1' href='manageCategories.php?id=" . $row['id'] .
                    "'>Edit</a>"; ?>
                    </td>
                    <td> <?php echo "<a class='link-white btn btn-danger px-2 py-1' href='?type=delete&id=" . $row['id'] .
                    "'>Delete</a>"; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>
<script type="text/javascript" src="js/admin.js"></script>

</body>
</html>
