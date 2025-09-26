<?php
include "header.php";
include "config.php";
include "util.php";
?>

<div class="container">
<?php
$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_POST['checkout_num'])) {
    $checkout_num = (int)$_POST['checkout_num'];

    $check_query = "SELECT checkout_return FROM checkout WHERE checkout_num = $checkout_num";
    $check_result = mysqli_query($conn, $check_query);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result);
        if ($row['checkout_return'] == '반납') {
            echo "<script>alert('이미 반납된 도서입니다.'); location.href='checkout_list.php';</script>";
        } else {
            mysqli_begin_transaction($conn);

            try {
                $query = "UPDATE checkout SET checkout_return = '반납' WHERE checkout_num = $checkout_num";
                if (!mysqli_query($conn, $query)) {
                    throw new Exception('Query Error (return book): ' . mysqli_error($conn));
                }

                $stock_query = "SELECT stock_num FROM checkout WHERE checkout_num = $checkout_num";
                $stock_res = mysqli_query($conn, $stock_query);
                if (!$stock_res) {
                    throw new Exception('Query Error (get stock_num): ' . mysqli_error($conn));
                }

                $stock_row = mysqli_fetch_assoc($stock_res);
                $stock_num = $stock_row['stock_num'];

                $update_stock_query = "UPDATE stock SET stock_borrow = '대출가능' WHERE stock_num = '$stock_num'";
                if (!mysqli_query($conn, $update_stock_query)) {
                    throw new Exception('Query Error (update stock): ' . mysqli_error($conn));
                }

                mysqli_commit($conn);

                echo "<script>alert('반납되었습니다.'); location.href='checkout_list.php';</script>";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo "<script>alert('" . $e->getMessage() . "'); history.back();</script>";
            }
        }
    } else {
        echo "<script>alert('해당 대출 기록을 찾을 수 없습니다.'); history.back();</script>";
    }
}

mysqli_close($conn);
?>
</div>
