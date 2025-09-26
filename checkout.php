<?php
include "header.php";
include "config.php";
include "util.php";
?>

<h2>대출</h2>
<div class="container">
    <form method="POST" action="">
        <div>
            <input type="text" id="stock_num" name="stock_num" placeholder="도서재고번호" required>
        </div>
        <div>
            <input type="text" id="mem_num" name="mem_num" placeholder="회원번호" required>
        </div>
        <button type="submit" name="borrow">대출하기</button>
    </form>
    <a href="checkout_list.php">대출내역확인</a>
</div>

<?php
$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_POST['borrow'])) {
    $stock_num = (int)$_POST['stock_num'];
    $mem_num = (int)$_POST['mem_num'];
    $reserve_num = isset($_POST['reserve_num']) ? (int)$_POST['reserve_num'] : null;
    $book_num = isset($_POST['book_num']) ? (int)$_POST['book_num'] : null;

    mysqli_begin_transaction($conn);

    try {
        $stock_check_query = $book_num
            ? "SELECT * FROM stock WHERE book_num = '$book_num' AND stock_borrow = '대출가능' LIMIT 1"
            : "SELECT * FROM stock WHERE stock_num = '$stock_num' AND stock_borrow = '대출가능' LIMIT 1";
        $stock_check_result = mysqli_query($conn, $stock_check_query);
        if (!$stock_check_result) {
            throw new Exception('Query Error (stock check): ' . mysqli_error($conn));
        }

        if (mysqli_num_rows($stock_check_result) > 0) {
            $stock = mysqli_fetch_assoc($stock_check_result);
            $stock_num = $stock['stock_num'];
            echo "<script>console.log('도서 대출 가능 확인 완료');</script>";

            $insert_checkout_query = "INSERT INTO checkout (checkout_date, checkout_return, stock_num, mem_num) VALUES (NOW(), '대출중', '$stock_num', '$mem_num')";
            if (!mysqli_query($conn, $insert_checkout_query)) {
                throw new Exception('Query Error (insert checkout): ' . mysqli_error($conn));
            }
            echo "<script>console.log('대출 내역 추가 완료');</script>";

            $update_stock_query = "UPDATE stock SET stock_borrow = '대출중' WHERE stock_num = '$stock_num'";
            if (!mysqli_query($conn, $update_stock_query)) {
                throw new Exception('Query Error (update stock): ' . mysqli_error($conn));
            }
            echo "<script>console.log('도서 상태 업데이트 완료');</script>";

            if ($reserve_num) {
                $update_reservation_query = "UPDATE reservation SET reserve_end = '대출완료' WHERE reserve_num = '$reserve_num'";
                if (!mysqli_query($conn, $update_reservation_query)) {
                    throw new Exception('Query Error (update reservation): ' . mysqli_error($conn));
                }
                echo "<script>console.log('예약 상태 업데이트 완료');</script>";
            }

            mysqli_commit($conn);

            echo "<script>alert('대출이 성공적으로 처리되었습니다.'); location.href='checkout.php';</script>";
        } else {
            throw new Exception('해당 도서는 대출이 불가능합니다.');
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }

    mysqli_close($conn);
}
?>
