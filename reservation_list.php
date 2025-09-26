<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_POST['delete'])) {
    $reserve_num = (int)$_POST['reserve_num'];

    mysqli_begin_transaction($conn);

    try {
        $delete_reservation_query = "DELETE FROM reservation WHERE reserve_num = '$reserve_num'";
        if (!mysqli_query($conn, $delete_reservation_query)) {
            throw new Exception('Query Error (delete reservation): ' . mysqli_error($conn));
        }

        mysqli_commit($conn);
        echo "<script>alert('예약이 성공적으로 삭제되었습니다.');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}

if (isset($_POST['cancel'])) {
    $reserve_num = (int)$_POST['reserve_num'];

    $check_status_query = "SELECT reserve_end FROM reservation WHERE reserve_num = '$reserve_num'";
    $check_status_result = mysqli_query($conn, $check_status_query);
    if ($check_status_result) {
        $status_row = mysqli_fetch_assoc($check_status_result);
        if ($status_row['reserve_end'] === '예약취소') {
            echo "<script>alert('이미 취소된 예약입니다.');</script>";
        } elseif ($status_row['reserve_end'] === '대출완료') {
            echo "<script>alert('대출완료 상태는 취소할 수 없습니다.');</script>";
        } else {
            mysqli_begin_transaction($conn);

            try {
                $cancel_reservation_query = "UPDATE reservation SET reserve_end = '예약취소' WHERE reserve_num = '$reserve_num'";
                if (!mysqli_query($conn, $cancel_reservation_query)) {
                    throw new Exception('Query Error (cancel reservation): ' . mysqli_error($conn));
                }

                mysqli_commit($conn);
                echo "<script>alert('예약이 성공적으로 취소되었습니다.');</script>";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo "<script>alert('" . $e->getMessage() . "');</script>";
            }
        }
    } else {
        echo "<script>alert('Query Error: " . mysqli_error($conn) . "');</script>";
    }
}

$member_name = isset($_POST['member_name']) ? $_POST['member_name'] : '';
$query = "SELECT reservation.reserve_num, member.mem_name, book.book_title, reservation.reserve_end, reservation.book_num, reservation.mem_num, MIN(stock.stock_num) AS stock_num 
          FROM reservation
          JOIN member ON reservation.mem_num = member.mem_num
          JOIN book ON reservation.book_num = book.book_num
          JOIN stock ON reservation.book_num = stock.book_num";

if ($member_name) {
    $query .= " WHERE member.mem_name LIKE '%$member_name%'";
}

$query .= " GROUP BY reservation.book_num";

$res = mysqli_query($conn, $query);
if (!$res) {
    die('Query Error: ' . mysqli_error($conn));
}
?>

<h2>예약내역</h2>
<div class="container">
    <form method="POST" action="">
        <input type="text" name="member_name" placeholder="회원명" value="<?php echo htmlspecialchars($member_name); ?>">
        <button type="submit">검색</button>
    </form>
</div>

<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th>번호</th>
                <th>회원명</th>
                <th>제목</th>
                <th>상태</th>
                <th>취소</th>
                <th>삭제</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $row_index = 1;
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_array($res)) {
                echo "<tr>";
                echo "<td>{$row_index}</td>";
                echo "<td>{$row['mem_name']}</td>";
                echo "<td><a href='book_detail.php?book_num={$row['book_num']}&stock_num={$row['stock_num']}'>{$row['book_title']}</a></td>";
                echo "<td>{$row['reserve_end']}</td>";
                echo "<td><form method='post' action=''>
                          <input type='hidden' name='reserve_num' value='{$row['reserve_num']}'>
                          <button class='button primary small' type='submit' name='cancel'>취소</button>
                      </form></td>";
                echo "<td><form method='post' action=''>
                          <input type='hidden' name='reserve_num' value='{$row['reserve_num']}'>
                          <button class='button primary small' type='submit' name='delete'>삭제</button>
                      </form></td>";
                echo "</tr>";
                $row_index++;
            }
        } else {
            echo "<tr><td colspan='6'>예약 내역이 없습니다.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
