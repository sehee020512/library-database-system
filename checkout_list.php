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

    $query = "DELETE FROM checkout WHERE checkout_num = $checkout_num AND checkout_return = '반납'";
    if (mysqli_query($conn, $query)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>alert('대출 기록이 삭제되었습니다.'); location.href='checkout_list.php';</script>";
        } else {
            echo "<script>alert('반납되지 않은 도서입니다. 반납 후 삭제해주세요.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('삭제 중 오류가 발생했습니다.'); history.back();</script>";
    }
}

mysqli_close($conn);
?>
</div>
