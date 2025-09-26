<?php
include "header.php";
include "config.php";
include "util.php";
?>

<h2>예약</h2>
<div class="container">
            <form method="POST" action="">
                <div>
                    <input type="text" id="book_num" name="book_num" placeholder="도서번호" required>
                </div>
                <div>
                    <input type="text" id="mem_num" name="mem_num" placeholder="회원번호" required>
                </div>
                <button type="submit" name="reserve">예약하기</button>
            </form><a href="reservation_list.php">예약내역확인</a>
</div>

<?php
$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_POST['reserve'])) {
    $book_num = (int)$_POST['book_num'];
    $mem_num = (int)$_POST['mem_num'];

    mysqli_begin_transaction($conn);

    try {
        $stock_check_query = "SELECT * FROM stock WHERE book_num = '$book_num' AND stock_borrow = '대출가능'";
        $stock_check_result = mysqli_query($conn, $stock_check_query);
        if (!$stock_check_result) {
            throw new Exception('Query Error (stock check): ' . mysqli_error($conn));
        }

        if (mysqli_num_rows($stock_check_result) > 0) {
            echo "<script>alert('대출 가능한 도서입니다. 예약할 수 없습니다.');</script>";
        } else {
            $stock_check_all_borrowed_query = "SELECT * FROM stock WHERE book_num = '$book_num' AND stock_borrow = '대출중'";
            $stock_check_all_borrowed_result = mysqli_query($conn, $stock_check_all_borrowed_query);
            if (!$stock_check_all_borrowed_result) {
                throw new Exception('Query Error (all stock check): ' . mysqli_error($conn));
            }

            $total_stock_count_query = "SELECT COUNT(*) as total_stock FROM stock WHERE book_num = '$book_num'";
            $total_stock_count_result = mysqli_query($conn, $total_stock_count_query);
            if (!$total_stock_count_result) {
                throw new Exception('Query Error (total stock count): ' . mysqli_error($conn));
            }
            $total_stock_row = mysqli_fetch_assoc($total_stock_count_result);
            $total_stock_count = $total_stock_row['total_stock'];

            if (mysqli_num_rows($stock_check_all_borrowed_result) == $total_stock_count) {
                $insert_reservation_query = "INSERT INTO reservation (reserve_end, book_num, mem_num) VALUES ('예약중', '$book_num', '$mem_num')";
                if (!mysqli_query($conn, $insert_reservation_query)) {
                    throw new Exception('Query Error (insert reservation): ' . mysqli_error($conn));
                }

                echo "<script>alert('예약이 성공적으로 처리되었습니다.');</script>";
            } else {
                throw new Exception('대출 가능한 도서가 있습니다. 예약할 수 없습니다.');
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }

    mysqli_close($conn);
}
?>
