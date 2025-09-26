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

    $stock_query = "SELECT * FROM stock WHERE stock_num = $stock_num";
    $stock_result = mysqli_query($conn, $stock_query);
    if ($stock_result) {
        $stock = mysqli_fetch_assoc($stock_result);
    } else {
        die('Query Error (stock): ' . mysqli_error($conn));
    }
} else {
    die('Invalid request');
}

mysqli_close($conn);
?>

<div class="container">
    <h2>대출 도서 정보</h2>
    <table class="table">
        <tr>
            <th>도서번호</th>
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
            <th>출판연도</th>
            <td><?php echo $book['book_publish_year']; ?></td>
        </tr>
        <tr>
        	<th>재고번호</th>
        	<td><?php echo $stock['stock_num']; ?></td>
        </tr>
    </table>
</div>

</body>
</html>
