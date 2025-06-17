<?php
include_once 'db.php';


$report_sql = "SELECT * FROM `report`";
$report_result = $conn->query($report_sql);
$report_data = $report_result->fetchAll(PDO::FETCH_ASSOC);

$count_a = "SELECT * FROM `report` WHERE `type` = 'a' GROUP BY `step`";
$count_a_result = $conn->query($count_a);
$count_a_data = $count_a_result->fetchAll(PDO::FETCH_ASSOC);
$total_a = count($count_a_data);

$count_b = "SELECT * FROM `report` WHERE `type` = 'b' GROUP BY `step`";
$count_b_result = $conn->query($count_b);
$count_b_data = $count_b_result->fetchAll(PDO::FETCH_ASSOC);
$total_b = count($count_b_data);

$revision_sql = "SELECT * FROM `revision_history`";
$revision_result = $conn->query($revision_sql);
$revision_data = $revision_result->fetchAll(PDO::FETCH_ASSOC);


if(isset($_SESSION['display'])) {
    $display = $_SESSION['display'];
    $update_time = $_SESSION['update_time'];
    unset($_SESSION['display']);
    unset($_SESSION['update_time']);
} else {
    $display = 'none';
    $update_time = '';
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['display'] = 'block';
    $_SESSION['update_time'] = date('Y-m-d H:i:s');

    $action = $_POST['action'];
    $step = $_POST['step'];
    $spec = $_POST['spec'];
    $type = $_POST['type'];
    $rev_revision = $_POST['rev_revision'];
    $eco_revision = $_POST['eco_revision'];
    $date_revision = $_POST['date_revision'];
    $action_revision = $_POST['action_revision'];
    $author_revision = $_POST['author_revision'];

    $delete_sql = "DELETE FROM `report`";
    $delete_stmt = $conn->prepare($delete_sql);

    $delete_rev_sql = "DELETE FROM `revision_history`";
    $delete_rev_stmt = $conn->prepare($delete_rev_sql);

    if($delete_rev_stmt->execute()) {
        foreach($rev_revision as $key => $value) {
            if(empty(trim($value))) {
                continue;
            }
            $sql = "INSERT INTO `revision_history` (`Rev`, `ECO`, `date`, `action`, `Author`) VALUES (:rev, :eco, :date, :rev_action, :rev_author)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':rev' => $rev_revision[$key],
                ':eco' => $eco_revision[$key],
                ':date' => $date_revision[$key],
                ':rev_action' => $action_revision[$key],
                ':rev_author' => $author_revision[$key]
            ]);
        }
    }


    if($delete_stmt->execute()) {
        foreach($action as $key => $value) {
            // 跳过空的 action
            if(empty(trim($value))) {
                continue;
            }
            $sql = "INSERT INTO `report` (`action`, `step`, `spec`, `type`) VALUES (:action, :step, :spec, :type)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':action' => $action[$key],
                ':step' => $step[$key],
                ':spec' => $spec[$key],
                ':type' => $type[$key]
            ]);
        }
    }

    header('Location: form.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Report System</title>

  <!-- Include Bootstrap (v5.3 or similar) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
  <style>
    .step-title {
      margin-top: 2rem;
      color: #0d6efd;
    }
  </style>
</head>
<body>

<?php include_once 'header.php'; ?>

<div class="container mt-4">
  <h1>Form Setting</h1>

  <?php if ($display === 'block') { ?>
  <div class="alert alert-success" id="success-alert">
    <h4 class="alert-heading">Success</h4>
    <p>The form has been updated successfully.</p>
    <hr>
    <p class="mb-0">Last updated: <?= $update_time ?></p>
  </div>
  <?php } ?>

  <!-- Tabs Navigation -->
  <ul class="nav nav-tabs" id="formTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="continuity-tab" data-bs-toggle="tab" data-bs-target="#continuity" type="button" role="tab">Continuity Checks</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button" role="tab">Final Verification</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="revision-tab" data-bs-toggle="tab" data-bs-target="#revision" type="button" role="tab">Revision History</button>
    </li>
  </ul>

  <form action="form.php" method="post">
    <div class="tab-content" id="formTabsContent">

      <!-- Tab 1: Continuity Checks -->
      <div class="tab-pane fade show active" id="continuity" role="tabpanel">
        <h4 class="step-title">Continuity Checks
          <span class="ms-3">
            <button class="btn btn-primary" type="button" onclick="add_step('a', <?= $total_a + 1 ?>)">+ Add Step</button>
            <button class="btn btn-danger" type="button" onclick="delete_step('a', <?= $total_a ?>)">- Delete Step</button>
            <label class="ms-3 text-secondary">Total Step: <?= $total_a ?></label>
          </span>
        </h4>

        <?php for ($i = 1; $i <= $total_a; $i++) { ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <h5>Step <?= $i ?></h5>
            <button class="btn" type="button" id="add-row<?= $i ?>"><i class="fa-solid fa-plus"></i></button>
          </div>

          <table class="table table-bordered table-striped">
            <?php foreach ($report_data as $report) {
              if ($report['step'] == $i && $report['type'] == 'a') { ?>
              <tr>
                <td class="w-50">
                  <input class="form-control" type="text" name="action[]" value="<?= $report['action'] ?>">
                  <input class="form-control" type="number" name="step[]" value="<?= $i ?>" hidden>
                  <input class="form-control" type="text" name="type[]" value="a" hidden>
                </td>
                <td class="w-50">
                  <div class="input-group">
                    <input class="form-control" type="text" name="spec[]" value="<?= $report['spec'] ?>">
                    <span class="input-group-text">Ohm</span>
                  </div>
                </td>
                <td class="text-center">
                  <button class="btn text-danger fs-5" type="button" onclick="del_data(<?= $report['id'] ?>)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php }} ?>
            <tr id="new_row<?= $i ?>" style="display: none;">
              <td>
                <input class="form-control" type="text" name="action[]" placeholder="Action">
                <input type="number" name="step[]" value="<?= $i ?>" hidden>
                <input type="text" name="type[]" value="a" hidden>
              </td>
              <td>
                <div class="input-group">
                  <input class="form-control" type="text" name="spec[]" placeholder="Spec">
                  <span class="input-group-text">Ohm</span>
                </div>
              </td>
              <td class="text-center fs-5">
                <a href="#" class="link-danger delete-row"><i class="fa-solid fa-minus mt-2"></i></a>
              </td>
            </tr>
          </table>
        </div>
        <?php } ?>
      </div>

      <!-- Tab 2: Final Verification -->
      <div class="tab-pane fade" id="final" role="tabpanel">
        <h4 class="step-title">Final Verification
          <span class="ms-3">
            <button class="btn btn-primary" type="button" onclick="add_step('b', <?= $total_b + 1 ?>)">+ Add Step</button>
            <button class="btn btn-danger" type="button" onclick="delete_step('b', <?= $total_b ?>)">- Delete Step</button>
            <label class="ms-3 text-secondary">Total Step: <?= $total_b ?></label>
          </span>
        </h4>

        <?php for ($i = 1; $i <= $total_b; $i++) { ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <h5>Step <?= $i + $total_a ?></h5>
            <button class="btn" type="button" id="add-row<?= $i + $total_a ?>"><i class="fa-solid fa-plus"></i></button>
          </div>

          <table class="table table-bordered table-striped">
            <?php foreach ($report_data as $report) {
              if ($report['step'] == $i && $report['type'] == 'b') { ?>
              <tr>
                <td>
                  <input class="form-control" type="text" name="action[]" value="<?= $report['action'] ?>">
                  <input type="number" name="step[]" value="<?= $i ?>" hidden>
                  <input type="text" name="spec[]" value="NA" hidden>
                  <input type="text" name="type[]" value="b" hidden>
                </td>
                <td class="text-center">
                  <button class="btn text-danger fs-5" type="button" onclick="del_data(<?= $report['id'] ?>)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php }} ?>
            <tr id="new_row<?= $i + $total_a ?>" style="display: none;">
              <td>
                <input class="form-control" type="text" name="action[]" placeholder="Action">
                <input type="number" name="step[]" value="<?= $i ?>" hidden>
                <input type="text" name="spec[]" value="NA" hidden>
                <input type="text" name="type[]" value="b" hidden>
              </td>
              <td class="text-center fs-5">
                <a href="#" class="link-danger delete-row"><i class="fa-solid fa-minus mt-2"></i></a>
              </td>
            </tr>
          </table>
        </div>
        <?php } ?>
      </div>

      <!-- Tab 3: Revision History -->
      <div class="tab-pane fade" id="revision" role="tabpanel">
        <h2 class="step-title">Revision History</h2>
        <div class="text-end">
          <button class="btn" type="button" id="add-rev"><i class="fa-solid fa-plus"></i></button>
        </div>

        <table class="table table-bordered table-striped mt-2">
          <thead>
            <tr>
              <th style="width: 8%;">Rev</th>
              <th style="width: 10%;">ECO</th>
              <th style="width: 10%;">Date</th>
              <th style="width: 55%;">Action</th>
              <th style="width: 15%;">Author</th>
              <th style="width: 2%;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($revision_data as $revision) { ?>
            <tr>
              <td><input class="form-control text-center" type="text" name="rev_revision[]" value="<?= $revision['Rev'] ?>"></td>
              <td><input class="form-control" type="text" name="eco_revision[]" value="<?= $revision['ECO'] ?>"></td>
              <td><input class="form-control" type="date" name="date_revision[]" value="<?= $revision['date'] ?>"></td>
              <td><textarea class="form-control" name="action_revision[]" rows="3"><?= $revision['action'] ?></textarea></td>
              <td><input class="form-control" type="text" name="author_revision[]" value="<?= $revision['Author'] ?>"></td>
              <td class="text-center">
                <button class="btn text-danger fs-5" type="button" onclick="del_rev(<?= $revision['id'] ?>)">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php } ?>
            <tr id="new_rev" style="display: none;">
              <td><input class="form-control text-center" type="text" name="rev_revision[]" placeholder="Rev"></td>
              <td><input class="form-control" type="text" name="eco_revision[]" placeholder="ECO"></td>
              <td><input class="form-control" type="date" name="date_revision[]" placeholder="Date"></td>
              <td><textarea class="form-control" name="action_revision[]" rows="3" placeholder="Action"></textarea></td>
              <td><input class="form-control" type="text" name="author_revision[]" placeholder="Author"></td>
              <td class="text-center fs-5">
                <a href="#" class="link-danger delete-row"><i class="fa-solid fa-minus mt-2"></i></a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <button class="btn btn-success mt-4 w-100" type="submit" onclick="showSuccessAlert()">Update</button>
  </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<script>

document.addEventListener('DOMContentLoaded', function() {
    var count = <?php echo $total_a; ?>;
    var count_b = <?php echo $total_b; ?>;

    var total = count + count_b;
    // 为每个添加按钮添加点击事件
    for(let i = 1; i <= total; i++) {
        const addButton = document.getElementById('add-row' + i);
        const templateRow = document.getElementById('new_row' + i);
        
        if(addButton && templateRow) {
            addButton.addEventListener('click', function() {
                // 克隆隐藏的行
                const newRow = templateRow.cloneNode(true);
                // 显示新行
                newRow.style.display = '';
                // 将新行插入到表格中
                templateRow.parentNode.insertBefore(newRow, templateRow);
            });
        }
    }

    // 修订历史表格的添加按钮
    const addButtonRev = document.getElementById('add-rev');
    const templateRowRev = document.getElementById('new_rev');

    if(addButtonRev && templateRowRev) {
        addButtonRev.addEventListener('click', function() {
            // 克隆隐藏的行
            const newRow = templateRowRev.cloneNode(true);
            // 显示新行
            newRow.style.display = '';
            // 将新行插入到表格中
            templateRowRev.parentNode.insertBefore(newRow, templateRowRev);
        });
    }

    // 为删除按钮添加事件委托
    document.addEventListener('click', function(e) {
        if(e.target.closest('.delete-row')) {
            e.target.closest('tr').remove();
        }
    });
});

function del_rev(id) {
    if(confirm('Are you sure you want to delete this data?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "delete_report.php?rev_id=" + id, true);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                // 找到包含这个 id 的按钮的父级 tr 元素并删除
                const deleteButton = document.querySelector(`button[onclick="del_rev(${id})"]`);
                if(deleteButton) {
                    deleteButton.closest('tr').remove();
                }
            }
        };
        xhttp.send();
    }
}


function del_data(id) {
    if(confirm('Are you sure you want to delete this data?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "delete_report.php?id=" + id, true);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                // 找到包含这个 id 的按钮的父级 tr 元素并删除
                const deleteButton = document.querySelector(`button[onclick="del_data(${id})"]`);
                if(deleteButton) {
                    deleteButton.closest('tr').remove();
                }
            }
        };
        xhttp.send();
    }
}


function delete_step(type, step) {
    if(confirm('Are you sure you want to delete One step?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "delete_step.php?type=" + type + "&step=" + step, true);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                location.reload();
            }
        };
        xhttp.send();
    }
}

function add_step(type, step) {
    if(confirm('Are you sure you want to add a new step?')) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "add_step.php?type=" + type + "&step=" + step, true);
        xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
                location.reload();
            }
        };
        xhttp.send();
    }
}

</script>
</html>