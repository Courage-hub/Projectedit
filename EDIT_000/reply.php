<div class="reply">
  <h4><?php echo $data['name']; ?></h4>
  <p><?php echo $data['date']; ?></p>
  <p><?php echo $data['comment']; ?></p>
  <?php $reply_id = $data['id']; ?>
  <button class="reply" onclick="responder(<?php echo $reply_id; ?>, '<?php echo $data['name']; ?>');">Responder</button>
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