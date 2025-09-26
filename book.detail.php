<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_GET['book_num']) && isset($_GET['stock_num'])) {
    $book_num = (int)$_GET['book_num'];
    $stock_num = (int)$_GET['stock_num'];

    $book_query = "SELECT * FROM book WHERE book_num = $book_num";
    $book_result = mysqli_query($conn, $book_query);
    if ($book_result) {
        $book = mysqli_fetch_assoc($book_result);
    } else {
        die('Query Error (book): ' . mysqli_error($conn));
    }

    $stock_query = "SELECT * FROM stock WHERE book_num = $book_num";
    $stock_result = mysqli_query($conn, $stock_query);
    if (!$stock_result) {
        die('Query Error (stock): ' . mysqli_error($conn));
    }
} else {
    die('Invalid request');
}

mysqli_close($conn);
?>

<div class="container">
    <h2>도서 상세 정보</h2>
    <table class="table">
        <tr>
            <th>도서 번호</th>
            <td><?php echo $book['book_num']; ?></td>
        </tr>
        <tr>
            <th>제목</th>
            <td><?php echo $book['book_title']; ?></td>
        </tr>
        <tr>
            <th>저자</th>
            <td><?php echo $book['book_author']; ?></td>
        </tr>
        <tr>
            <th>출판사</th>
            <td><?php echo $book['book_publisher']; ?></td>
        </tr>
        <tr>
            <th>출판 연도</th>
            <td><?php echo $book['book_publish_year']; ?></td>
        </tr>
    </table>

    <h3>도서 재고 상태</h3>
    <table class="table">
        <thead>
            <tr>
                <th>재고 번호</th>
                <th>대출 상태</th>
            </tr>
        </thead>
        <tbody>
        <?php
        while ($stock = mysqli_fetch_array($stock_result)) {
            echo "<tr>";
            echo "<td>{$stock['stock_num']}</td>";
            echo "<td>{$stock['stock_borrow']}</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
