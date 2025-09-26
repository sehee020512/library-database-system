<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

$query = "
    SELECT DISTINCT book.book_title, member.mem_name, reservation.reserve_end, reservation.reserve_num, reservation.book_num, reservation.mem_num
    FROM book
    JOIN reservation ON book.book_num = reservation.book_num
    JOIN member ON reservation.mem_num = member.mem_num
    JOIN stock ON book.book_num = stock.book_num
    WHERE stock.stock_borrow = '대출가능' AND reservation.reserve_end = '예약중'
";

$res = mysqli_query($conn, $query);
if (!$res) {
    die('Query Error: ' . mysqli_error($conn));
}
?>

<div class="container">
    <h2>예약자 중 대출가능 알림</h2>
    <table class="table">
        <thead>
            <tr>
                <th>제목</th>
                <th>예약자명</th>
                <th>대출</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_array($res)) {
                echo "<tr>";
                echo "<td>{$row['book_title']}</td>";
                echo "<td>{$row['mem_name']}</td>";
                echo "<td><form method='post' action='checkout.php'>
                          <input type='hidden' name='reserve_num' value='{$row['reserve_num']}'>
                          <input type='hidden' name='book_num' value='{$row['book_num']}'>
                          <input type='hidden' name='mem_num' value='{$row['mem_num']}'>
                          <button class='button primary small' type='submit' name='borrow'>대출</button>
                      </form></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>대출 가능한 도서 중 예약된 도서가 없습니다.</td></tr>";
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
