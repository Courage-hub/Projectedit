<?php
$conn = mysqli_connect("localhost", "root", "", "edit_0006");

if(isset($_POST["submit"])){
  $titulo = $_POST["titulo"];
  $name = $_POST["name"];
  $comment = $_POST["comment"];
  $reply_id = $_POST["reply_id"];
  $date = date('F d Y, h:i:s A');
  $query = "INSERT INTO tb_data VALUES('', '$name', '$comment', '$date', '$reply_id', '$titulo')";
  mysqli_query($conn, $query);
}
?>
<html>
  <head>
  <title>Forvia - Foro</title>
    <meta charset="UTF-8">
    <meta name="author" content="rage">
    <link rel="stylesheet" href="css/Proyecto.css">
    <link rel="stylesheet" href="css/button/button.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
  </head>
  <style>
    .button-5 {
    align-items: center;
    background-clip: padding-box;
    background-color: #FA0057;
    border: 1px solid transparent;
    border-radius: .25rem;
    box-shadow: rgba(0, 0, 0, 0.02) 0 1px 3px 0;
    box-sizing: border-box;
    color: #fff;
    cursor: pointer;
    display: inline-flex;
    font-family: system-ui,-apple-system,system-ui,"Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 16px;
    font-weight: 600;
    justify-content: center;
    line-height: 1.25;
    margin: 20px;
    min-height: 3rem;
    padding: calc(.875rem - 1px) calc(1.5rem - 1px);
    position: relative;
    text-decoration: none;
    transition: all 250ms;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    vertical-align: baseline;
    width: auto;
  }
  
  .button-5:hover,
  .button-5:focus {
    background-color: #ff2f78;
    box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
  }
  
  .button-5:hover {
    transform: translateY(-1px);
  }
* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'Kanit', sans-serif;
    margin: 0;
    padding: 0;
    background: #212523;

}

    .container{
      background: white;
      width: 800px;
      margin: 0 auto;
      padding-top: 1px;
      padding-bottom: 5px;
    }
    .comment, .reply{
      margin-top: 5px;
      padding: 10px;
      border-bottom: 1px solid black;
    }
    .reply{
      border: 1px solid #ccc;
    }
    p{
      margin-top: 5px;
      margin-bottom: 5px;
    }
    form{
      margin: 10px;
    }
    form h3{
      margin-bottom: 5px;
    }
    form input, form textarea{
      width: 100%;
      padding: 5px;
      margin-bottom: 10px;
    }
    form button.publicar, button{
      background: #4CAF50;
      color: white;
      cursor: pointer;
      font-family: 'Kanit', sans-serif;
      border-radius:20px;
      padding: 10px 20px;
      width: 25%;
    }
    form button.cancelar, button{
      background: red;
      font-family: 'Kanit', sans-serif;
      color: white;
      cursor: pointer;
      border-radius:20px;
      padding: 10px 20px;
      width: 25%;
    }
    button.reply{
      background: orange;
    }
  </style>
  <body>
    <div class="container">
      <?php
      $datas = mysqli_query($conn, "SELECT * FROM tb_data WHERE reply_id = 0"); 
      foreach($datas as $data) {
        require 'comment.php';
      }
      ?>
      <form id = "Formulario" method = "post">
        <h3 id = "title">Deja tu comentario!</h3>
        <input type="hidden" name="reply_id" id="reply_id">
        <input type="text" name="titulo" placeholder="Titulo">
        <input type="text" name="name" placeholder="Nombre">
        <textarea name="comment" placeholder="Comentario"></textarea>
        <button class = "publicar" type="submit" name="submit">Publicar</button>
        <button class = "cancelar" type="" name="cancelar">Cancelar</button>
      </form>
    </div>
    <a href="">
    <button class="button-5" role="button">Volver</button>
    </a>

    <script>
      function reply(id, name){
        title = document.getElementById('title');
        title.innerHTML = "Responder a " + name;
        document.getElementById('reply_id').value = id;
      }
    </script>

<script>
function resetForm() {
  document.getElementById("Formulario").reset();
}
</script>

  </body>
</html>
