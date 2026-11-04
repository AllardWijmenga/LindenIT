<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (!isLoggedIn()) {
  header('Location: index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['event-type'], $_POST['event-location'], $_POST['event-description'])) {
    $type = trim($_POST['event-type']);
    $location = trim($_POST['event-location']);
    $description = trim($_POST['event-description']);
  }

  if (!empty($type) && !empty($location) && !empty($description)) {
    try {
      $stmt = $pdo->prepare("INSERT INTO events (type, location, description) VALUES (?, ?, ?)");
      $stmt->execute([$type, $location, $description]);
    } catch (PDOException $e) {
      // Handle the error appropriately
      echo "Something went wrong: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GridWatch 2030</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- APP -->
  <div id="app-view">
    <?php
    // Header is stored in includes/header.html
    include __DIR__ . '/includes/header.html'; ?>

    <main>
      <!-- Overzicht -->
      <section id="tab-overzicht" class="tab">
        <?php
        $stationQuery = $pdo->query('SELECT * FROM stations');
        ?>
        <h2>Transformatorstations</h2>
        <table id="stations-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Naam</th>
              <th>Locatie</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $stationQuery->fetch()) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['id']) . '</td>';
              echo '<td>' . htmlspecialchars($row['name']) . '</td>';
              echo '<td>' . htmlspecialchars($row['location']) . '</td>';
              echo '<td>' . htmlspecialchars($row['status']) . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
        <form method="post" action="LindenFrontPage.php">
          <button id="refresh" class="ghost">Vernieuwen</button>
        </form>
      </section>

      <!-- Storingen -->
      <section id="tab-storingen" class="tab" hidden>
        <?php
        $eventQuery = $pdo->query('SELECT * FROM events ORDER BY timestamp DESC');
        ?>

        <form method="POST" action="LindenFrontPage.php" novalidate>
          <div class="form-group">
            <label class="form-label" for="event-type">Soort event</label>
            <input type="text" maxlength="99" id="event-type" name="event-type" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="event-location">Locatie</label>
            <input type="text" maxlength="99" id="event-location" name="event-location" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="event-description">Beschrijving</label>
            <input type="text" maxlength="255" id="event-description" name="event-description" class="form-input" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
            Stuur
          </button>
        </form>

        <h2>Storingen en meldingen</h2>
        <table id="alerts-table">
          <thead>
            <tr>
              <th>Tijdstip</th>
              <th>Type</th>
              <th>Locatie</th>
              <th>Beschrijving</th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $eventQuery->fetch()) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['timestamp']) . '</td>';
              echo '<td>' . htmlspecialchars($row['type']) . '</td>';
              echo '<td>' . htmlspecialchars($row['location']) . '</td>';
              echo '<td>' . htmlspecialchars($row['description']) . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
        <form method="post" action="LindenFrontPage.php">
          <button id="refresh-alerts" class="ghost">Vernieuwen</button>
        </form>
      </section>

      <!-- Scenarios -->
      <section id="tab-sensoren" class="tab" hidden>
        <h2>Scenarios</h2>


        <!-- <table id="sensor-table">
          <thead>
            <tr>
              <th>Station</th>
              <th>Vermogen (MW)</th>
              <th>Temperatuur (°C)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table> -->
        <form method="post" action="LindenFrontPage.php">
          <button id="refresh-sensors" class="ghost">Vernieuwen</button>
        </form>
      </section>


    </main>
  </div>
</body>

</html>