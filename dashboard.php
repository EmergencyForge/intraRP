<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= __('external_dashboard.title', [SYSTEM_NAME]) ?></title>
  <!-- Stylesheets -->
  <link rel="stylesheet" href="/assets/css/style.min.css" />
  <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
  <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
  <!-- Bootstrap -->
  <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
  <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
  <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
  <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
  <link rel="manifest" href="/assets/favicon/site.webmanifest" />
  <!-- Metas -->
  <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
  <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
  <meta property="og:url" content="https://<?php echo SYSTEM_URL ?>/dashboard.php" />
  <meta property="og:title" content="<?= __('metas.title', [SYSTEM_NAME, SERVER_CITY]) ?>" />
  <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
  <meta property="og:description" content="<?= __('metas.description', [RP_ORGTYPE, SERVER_CITY]) ?>" />
</head>

<body data-bs-theme="dark" id="dashboard" class="container-full position-relative">
  <div class="container-full mx-5">
    <div class="row mt-3">
      <div class="col-4 mx-auto text-center">
        <img src="<?php echo SYSTEM_LOGO ?>" alt="<?php echo SYSTEM_NAME ?>" style="height:128px;width:auto">
      </div>
    </div>

    <div class="row">
      <div class="col" id="cards">
        <?php
        require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
        $stmt = $pdo->prepare("SELECT * FROM intra_dashboard_categories ORDER BY priority ASC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
          $stmt2 = $pdo->prepare("SELECT * FROM intra_dashboard_tiles WHERE category = :category_id ORDER BY priority ASC");
          $stmt2->bindParam(':category_id', $row['id']);
          $stmt2->execute();
          $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        ?>
          <div class="mb-5">
            <div class="row">
              <div class="col mb-3">
                <h2><?= $row['title'] ?></h2>
              </div>
            </div>

            <?php
            $chunkedTiles = array_chunk($result2, 6);
            foreach ($chunkedTiles as $tileRow) {
            ?>
              <div class="row mb-3">
                <?php foreach ($tileRow as $tile) { ?>
                  <div class="col-md-2"> <!-- 12 / 6 = 2 per tile -->
                    <a href="<?= $tile['url'] ?>">
                      <div class="card h-100">
                        <div class="card-body">
                          <div class="card-fa mb-3 text-center d-block">
                            <i class="<?= $tile['icon'] ?>"></i>
                          </div>
                          <h5 class="card-title text-center fw-bold">
                            <?= $tile['title'] ?>
                          </h5>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php } ?>
              </div>
            <?php } ?>
          </div>
        <?php }
        if ($stmt->rowCount() == 0) {
          echo '<div class="alert alert-warning" role="alert">' . __('external_dashboard.not_configured') . '</div>';
        } ?>
      </div>
    </div>

  </div>
  <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/footer.php"; ?>
</body>

</html>