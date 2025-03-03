<html lang="en">

<head>
  <title>Forvia</title>
  <meta charset="UTF-8">
  <meta name="author" content="courage">
  <link rel="stylesheet" href="css/button/button.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
</head>
<style>
  @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  #inicio {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    flex-direction: column;
  }

  body {
    font-family: "Poppins", sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    /* Mínimo alto de la ventana */
  }

  .navbar {
    width: 100%;
    box-shadow: 0 1px 4px rgb(146 161 176 / 15%);

  }

  .nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 62px;
  }

  .navbar .menu-items {
    display: flex;
  }

  .navbar .nav-container li {
    list-style: none;
  }

  .navbar .nav-container a {
    text-decoration: none;
    color: #310e29;
    font-weight: 500;
    font-size: 1.2rem;
    padding: 0.7rem;
  }

  .navbar .nav-container a:hover {
    font-weight: bolder;
  }

  .nav-container {
    display: block;
    position: relative;
    height: 60px;
  }

  .nav-container .checkbox {
    position: absolute;
    display: block;
    height: 32px;
    width: 32px;
    top: 20px;
    left: 20px;
    z-index: 5;
    opacity: 0;
    cursor: pointer;
  }

  .nav-container .hamburger-lines {
    display: block;
    height: 26px;
    width: 32px;
    position: absolute;
    top: 17px;
    left: 20px;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .nav-container .hamburger-lines .line {
    display: block;
    height: 4px;
    width: 100%;
    border-radius: 10px;
    background: #0e2431;
  }

  .nav-container .hamburger-lines .line1 {
    transform-origin: 0% 0%;
    transition: transform 0.4s ease-in-out;
  }

  .nav-container .hamburger-lines .line2 {
    transition: transform 0.2s ease-in-out;
  }

  .nav-container .hamburger-lines .line3 {
    transform-origin: 0% 100%;
    transition: transform 0.4s ease-in-out;
  }

  .navbar .menu-items {
    padding-top: 120px;
    background-color: white;
    height: 100vh;
    width: 100vh;
    transform: translate(-150%);
    display: flex;
    flex-direction: column;
    transition: transform 0.5s ease-in-out;
    text-align: center;
  }

  .navbar .menu-items li {
    margin-bottom: 1.2rem;
    font-size: 1.5rem;
    font-weight: 500;
  }

  .logo {
    position: absolute;
    top: 5px;
    right: 15px;
    font-size: 1.2rem;
    color: #0e2431;
  }

  .nav-container input[type="checkbox"]:checked~.menu-items {
    transform: translateX(0);
  }

  .nav-container input[type="checkbox"]:checked~.hamburger-lines .line1 {
    transform: rotate(45deg);
  }

  .nav-container input[type="checkbox"]:checked~.hamburger-lines .line2 {
    transform: scaleY(0);
  }

  .nav-container input[type="checkbox"]:checked~.hamburger-lines .line3 {
    transform: rotate(-45deg);
  }

  .nav-container input[type="checkbox"]:checked~.logo {
    display: none;
  }

  table {
    width: 95%;

  }

  td,
  tr,
  th {
    border: 1px solid #dddddd;
    padding: 20px;
  }

  .id {
    width: 3%;
  }

  .name {
    width: 50px;
  }

  .dat {
    width: 10px;
  }

  .shares {
    width: 25px;
  }
</style>

<body>
  <nav>
    <div class="navbar">
      <div class="container nav-container">
        <input class="checkbox" type="checkbox" name="" id="" />
        <div class="hamburger-lines">
          <span class="line line1"></span>
          <span class="line line2"></span>
          <span class="line line3"></span>
        </div>
        <div class="logo">
          <h1>Forvia</h1>
        </div>
        <div class="menu-items">
          <li><a href="ProyectoFP.html">Home</a></li>
        </div>
      </div>
    </div>
  </nav>
  <?php
  include("conexion.php");

  $id = isset($_GET['id']) ? $_GET['id'] : '';
  $instruccion = isset($_GET['instruccion']) ? $_GET['instruccion'] : '';

  $query = "SELECT * FROM fpproject";
  if ($id || $instruccion) {
    $query .= " WHERE";
    if ($id) {
      $query .= " id = '$id'";
    }
    if ($id && $instruccion) {
      $query .= " AND";
    }
    if ($instruccion) {
      $query .= " instruccion LIKE '%$instruccion%'";
    }
  }

  $result = mysqli_query($conn, $query);

  ?>
  <div id="inicio">
    <br>
    <br>
    <br>
    <br>
    <h1>Client Task - Forvia </h1>
    <a href="agregar.php">+ New</a> <br><br>

    <form action="" method="GET">
      <input type="text" name="id" placeholder="Search by ID" value="<?php echo htmlspecialchars($id); ?>">
      <input type="text" name="instruccion" placeholder="Search by Instruction" value="<?php echo htmlspecialchars($instruccion); ?>">
      <button type="submit">Search</button>
    </form>
    <br>

    <table>
      <thead>
        <tr>
          <th class="id">ID</th>
          <th class="title">Instruction</th>
          <!-- <th class="name">Name</th> -->
          <th class="dat">Date</th>
          <th class="shares">Shares</th>
        </tr>
      </thead>

      <tbody>
        <?php
        while ($filas = mysqli_fetch_assoc($result)) {
        ?>
          <tr>
            <td class="id"><?php echo $filas['id'] ?></td>
            <td class="tit"><?php echo $filas['instruccion'] ?></td>
            <!-- <td>?php echo $filas['usuario'] ?></td>  -->
            <td><?php echo date('d/m/Y H:i:s', strtotime($filas['date'])); ?></td>
            <td><?php echo "<a href='editor.php?id=" . $filas['id'] . "'>Edit</a>";  ?>

              <?php
                echo "<a href='eliminar.php?id=" . $filas['id'] . "' onclick=\"return confirm('¿Are you sure you want to delete this content?')\">Delete</a>";
                ?></td> 
      
            </td>

            </td>
          </tr>
        <?php
        }
        ?>
      </tbody>

    </table>
    <?php
    mysqli_close($conn);
    ?>
  </div>
</body>


</html>