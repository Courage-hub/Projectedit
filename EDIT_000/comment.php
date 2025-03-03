<div class="comment">
  <h1><?php echo $data['titulo']; ?></h1>
  <p><?php echo $data['comment']; ?></p>
  <h4><?php echo $data['name']; ?></h4>
  <?php $reply_id = $data['id']; ?>
  <p class="date"><?php echo $data['date']; ?></p>

  <?php echo"<a href='editor.php'>Editor<a>"; ?>
  <button class="reply" onclick="reply(<?php echo $reply_id; ?>, '<?php echo $data['name']; ?>');">Reply</button>
  <?php
  unset($datas);
  $datas = mysqli_query($conn, "SELECT * FROM tb_data WHERE reply_id = $reply_id");
  if (mysqli_num_rows($datas) > 0) {
    foreach ($datas as $data) {
      require 'reply.php';
    }
  }
  ?>
</div>


<style>
.date{
  font-size: small;
  text-align: end;
  color: gray;
}

</style>