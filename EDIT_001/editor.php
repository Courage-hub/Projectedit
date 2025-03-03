<?php
    include ("conexion.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forvia - Edit</title>
</head>

<body>
<?php
    if (isset($_POST['enviar'])){
        $id=$_POST['id'];
        $nombre=$_POST['usuario'];
        $titulo=$_POST['instruccion'];

        $sql="update fpproject set usuario='".$nombre."',instruccion='".$titulo."' where idtareas='".$id."'";
        $result=mysqli_query($conn, $sql);
        

        if($result){
            echo"<script language='JavaScript'>
            alert ('The data was updated correctly');
            location.assign ('index.php');
            </script>";


        }else{
            echo"<script language='JavaScript'>
            alert ('Data was not updated correctly');
            location.assign ('index.php');
            </script>";

        }
        mysqli_close($conn);

    }else{ 
        $id=$_GET['id'];
        $sql="select * from fpproject where idtareas='".$id."'";
        $result=mysqli_query($conn, $sql);
        
        $fila=mysqli_fetch_assoc($result);
        $nombre=$fila["usuario"];
        $titulo=$fila ["instruccion"];

        mysqli_close($conn)





?>





    <h1>Editar</h1>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <label>Name</label>
        <input type="text" name="nombre" value="<?php echo $nombre; ?>"> <br>

        <label>Title</label>
        <input type="text" name="titulo" value="<?php echo $titulo; ?>"> <br>

        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <input type="submit" name="enviar" value="Actualizar">

        <a href="index.php">Back</a>

    </form>
<?php
    }
?>

</body>

</html>