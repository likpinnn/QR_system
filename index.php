<?php
include_once "db.php";


$report_sql = "SELECT * FROM `report`";
$report_result = $conn->query($report_sql);
$report_data = $report_result->fetchAll(PDO::FETCH_ASSOC);
$total = count($report_data);

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

$bai_sql = "SELECT * FROM `bai_no`";
$bai_result = $conn->query($bai_sql);
$bai_data = $bai_result->fetchAll(PDO::FETCH_ASSOC);

$rev_sql = "SELECT * FROM `rev`";
$rev_result = $conn->query($rev_sql);
$rev_data = $rev_result->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Report System</title>
    <!-- 添加Select2的CSS -->
    <link href="assets/css/select2.min.css" rel="stylesheet" />
    <!-- 添加 SignaturePad 库 -->
    <script src="assets/js/signature_pad.umd.min.js"></script>
    <style>
        .main-step {
            display: none;
            animation: fadeIn 0.5s;
        }
        .main-step.active {
            display: block;
        }
        .sub-step {
            display: none;
            animation: fadeIn 0.5s;
        }
        .sub-step.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .progress {
            height: 10px;
            margin-bottom: 30px;
        }
        .main-step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
        }
        .main-step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-weight: bold;
            position: relative;
        }
        .main-step-dot.active {
            background: #0d6efd;
            color: white;
        }
        .main-step-dot.completed {
            background: #198754;
            color: white;
        }
        .sub-step-indicator {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 0 20px;
        }
        .sub-step-dot {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 12px;
        }
        .sub-step-dot.active {
            background: #0d6efd;
            color: white;
        }
        .sub-step-dot.completed {
            background: #198754;
            color: white;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .step-title {
            margin-bottom: 50px;
            color: #0d6efd;
        }
        /* 添加签名画板样式 */
        .signature-pad {
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            width: 100%;
            max-width: 400px;
            height: 200px;
            touch-action: none;
            cursor: crosshair;
            object-fit: cover;
        }
        .signature-pad-container {
            margin-bottom: 1rem;
            width: 100%;
            max-width: 400px;
        }
        .signature-pad-actions {
            margin-top: 10px;
        }
        /* 添加图片预览样式 */
        #logo-preview {
            min-height: 200px;
            border: 1px dashed #ccc;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            height: 88%;
        }
        #logo-preview img {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
        }
        #logo-preview:empty::before {
            content: 'Preview will appear here';
            color: #6c757d;
        }
        /* 添加新的样式 */
        .logo-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 50px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .logo-upload-container:hover {
            border-color: #0d6efd;
            background-color: #f1f8ff;
        }
        .logo-upload-container.dragover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .logo-upload-icon {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .logo-upload-text {
            color: #6c757d;
            margin-bottom: 15px;
        }
        .logo-preview-container {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: white;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-preview-container img {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            padding: 10px;
        }
        .custom-file-upload {
            display: inline-block;
            padding: 8px 20px;
            background-color: #0d6efd;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .custom-file-upload:hover {
            background-color: #0b5ed7;
        }
        #logo {
            display: none;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            font-weight: normal;
        }
        .btn-primary{
            width: 135px;
            font-size: 16px;
            border-radius: 2rem !important;
        }
    </style>
</head>
<?php 
    include_once 'header.php'; 
    include_once 'loading.php';
?>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p class="fs-2 mt-2"><i class="fa-solid fa-user me-1"></i><i>Hi, Welcome <?php echo $_SESSION['username']; ?></i></p>
            </div>
        </div>

        <div class="row" style="margin-bottom: 100px;">
            <div class="col-md-12">
                <h1>Add New Report</h1>
            </div>
            <div id="add-report" class="mt-4">
                <!-- 主步骤进度条 -->
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>

                <!-- 主步骤指示器 -->
                <div class="main-step-indicator">
                    <div class="main-step-dot active" data-step="1">1</div>
                    <div class="main-step-dot" data-step="2">2</div>
                    <div class="main-step-dot" data-step="3">3</div>
                    <div class="main-step-dot" data-step="4">4</div>
                    <div class="main-step-dot" data-step="5">5</div>
                </div>

                <form action="result.php" method="post" enctype="multipart/form-data">
                    <!-- Step 1: Technical Information -->
                    <div class="main-step active" data-step="1">
                        <h2 class="step-title">Technical Information</h2>
                        <div class="form-group mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="logo" class="form-label">Logo</label>
                                    <div class="logo-upload-container" id="drop-zone">
                                        <div class="logo-upload-icon">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </div>
                                        <div class="logo-upload-text">
                                            <p>Drag and drop your logo here<br>or</p>
                                        </div>
                                        <label for="logo" class="custom-file-upload">
                                            <i class="fa-solid fa-upload me-2"></i>Choose File
                                        </label>
                                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                                        <input type="hidden" id="existing_logo" name="existing_logo" value="">
                                        <script>
                                            <?php
                                            $session_dir = 'assets/img/' . $_SESSION['username'] . '.png';
                                            if (file_exists($session_dir)) {
                                                echo "document.getElementById('existing_logo').value = '" . $session_dir . "';";
                                                echo "document.getElementById('logo').required = false;";
                                            }
                                            ?>
                                        </script>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Preview</label>
                                    <div class="logo-preview-container" id="logo-preview">
                                        <?php
                                        $session_dir = 'assets/img/' . $_SESSION['username'] . '.png';
                                        if (file_exists($session_dir)) {
                                            echo '<img src="' . $session_dir . '" alt="Logo Preview" style="max-width: 100%; max-height: 200px;">';
                                        } else {
                                            echo '<div class="text-muted">Preview will appear here</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <label for="">Report Name</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="report-name">Title 1</label>     
                                    <input type="text" class="  form-control" id="report-name" name="report-name1" value="Quality Report," placeholder="Title 1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="report-name">Title 2</label>     
                                    <input type="text" class="form-control" id="report-name2" name="report-name2" value="ASSY, PCB, XPS CARRIAGE" placeholder="Title 2" required>  
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="tools">Tools</label>
                            <input type="text" class="form-control" id="tools" name="tools" value="Multimeter" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="reference-document">Reference document</label>
                            <input type="text" class="form-control" id="reference-document" name="reference-document" value="332949-SX - SCHEMATIC, PCB, XPS CARRIAGE" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="bai_no">Brooks P/N & Revsion</label>
                            
                            <select name="bai_no" id="bai_no" class="form-control">
                                <option value="">Select Brooks P/N</option>
                                <?php foreach ($bai_data as $bai) { ?>
                                    <option value="<?php echo $bai['bai_no']; ?>"><?php echo $bai['bai_no']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label for="rev">REV</label>
                            <select name="rev" id="rev" class="form-control">
                                <option value="">Select Brooks REV</option>
                                <?php foreach ($rev_data as $rev) { ?>
                                    <option value="<?php echo $rev['rev']; ?>"><?php echo $rev['rev']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label for="serial-number">Brooks' Serial Number</label>
                            <input type="text" class="form-control" id="serial-number" name="serial-number" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="date-completed">Date Completed</label>
                            <input type="date" class="form-control" id="date-completed" name="date-completed" required>
                        </div>
                        <div class="form-group mb-4">
                            <label>Carriage PCB</label><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pcb-part-number">Brooks Part Number</label>
                                    <input type="text" class="form-control" id="pcb-part-number" name="pcb-part-number" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="pcb-serial-number">Supplier Serial Number</label>
                                    <input type="text" class="form-control" id="pcb-serial-number" name="pcb-serial-number" required>
                                </div>
                            </div>
                        </div>
                        

                    </div>
                    <!-- Step 2: Continuity Checks -->
                    <div class="main-step" data-step="2">
                        <h2 class="step-title">Continuity Checks</h2>
                        
                        <!-- 子步骤指示器 -->
                        <div class="sub-step-indicator">
                            <?php for($i=1; $i<=$total_a; $i++){?>
                                <div class="sub-step-dot<?php echo $i==1 ? ' active' : ''; ?>" data-sub-step="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </div>
                            <?php }?>
                        </div>

                        <!-- 子步骤内容 -->
                        <?php for($i=1; $i<=$total_a; $i++){?>
                            <div class="sub-step<?php echo $i==1 ? ' active' : ''; ?>" data-sub-step="<?php echo $i; ?>">
                                <h3 class="mb-3">Step <?php echo $i; ?></h3>
                                <?php foreach ($report_data as $report) { 
                                    if($report['step'] == $i && $report['type'] == 'a') {
                                ?>
                                    <div class="form-group mb-4">
                                        <label><?php echo $report['action']; ?></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="step<?php echo $i; ?>[]" required autocomplete="off">
                                            <input type="text" id="spec" value="<?php echo $report['spec']; ?>" hidden>
                                            <span class="input-group-text" id="basic-addon2">Ohm</span>
                                        </div>
                                    </div>
                                <?php } }?>
                            </div>
                        <?php }?>
                    </div>

                    <!-- Step 3: Final Verification -->
                    <div class="main-step" data-step="3">
                        <h2 class="step-title">Final Verification</h2>
                        
                        <!-- 子步骤指示器 -->
                        <div class="sub-step-indicator">
                            <?php for($i=1; $i<=$total_b; $i++){?>
                                <div class="sub-step-dot<?php echo $i==1 ? ' active' : ''; ?>" data-sub-step="<?php echo $i+$total_a; ?>">
                                    <?php echo $i+$total_a; ?>
                                </div>
                            <?php }?>
                        </div>

                        <!-- 子步骤内容 -->
                        <?php for($i=1; $i<=$total_b; $i++){?>
                            <div class="sub-step<?php echo $i==1 ? ' active' : ''; ?>" data-sub-step="<?php echo $i+$total_a; ?>">
                                <h3 class="mb-3">Step <?php echo $i+$total_a; ?></h3>
                                <?php foreach ($report_data as $report) { 
                                    if($report['step'] == $i && $report['type'] == 'b') {
                                ?>
                                    <div class="form-group mb-4">
                                        <label><?php echo $report['action']; ?></label>
                                        <select name="step<?php echo $i+$total_a; ?>[]" id="step<?php echo $i+$total_a; ?>" class="form-control">
                                            <option value="Pass">Pass</option>
                                            <option value="Fail">Fail</option>
                                        </select>
                                    </div>
                                <?php } }?>
                            </div>
                        <?php }?>
                    </div>

                    <!-- Step 4: Revision History -->
                    <div class="main-step" data-step="4">
                        <h2 class="step-title">Revision History</h2>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width: 10%;">Rev</th>
                                <th style="width: 10%;">ECO</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 55%;">Action</th>
                                <th style="width: 15%;">Author</th>
                            </tr>
                            <?php foreach ($revision_data as $revision) { ?>
                            <tr>
                                <td>
                                    <?php echo $revision['Rev']; ?>
                                </td>
                                <td>
                                    <?php echo $revision['ECO']; ?>
                                </td>
                                <td>
                                    <?php echo $revision['date']; ?>
                                </td>
                                <td>
                                    <?php echo $revision['action']; ?>
                                </td>
                                <td>
                                    <?php echo $revision['Author']; ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr id="new_rev" style="display: none;">
                                <td>
                                    <input class="form-control text-center" type="text" name="rev_revision[]" placeholder="Rev">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="eco_revision[]" placeholder="ECO">
                                </td>
                                <td>
                                    <input class="form-control" type="date" name="date_revision[]" placeholder="Date">
                                </td>
                                <td>
                                    <textarea class="form-control" name="action_revision[]" rows="3" placeholder="Action"></textarea>
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="author_revision[]" placeholder="Author">
                                </td>
                                <td class="text-center fs-5">
                                    <a href="javascript:void(0)" class="link-danger link-underline link-underline-opacity-0 delete-row">
                                        <i class="fa-solid fa-minus mt-2"></i>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Step 5: Completion signature -->
                    <div class="main-step" data-step="5">
                        <h2 class="step-title">Completion</h2>
                        
                        <div class="form-group mb-4">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $_SESSION['name']; ?>" required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label>Completion signature</label>
                            <div class="signature-pad-container">
                                <canvas id="signature-pad" class="signature-pad"></canvas>
                                <div class="signature-pad-actions">
                                    <button type="button" class="btn btn-secondary btn-sm" id="clear-signature">Clear</button>
                                </div>
                                <input type="hidden" name="signature" id="signature-input">
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="Date">Date</label>
                            <input type="date" class="form-control" id="Date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <!-- 导航按钮 -->

                    <div class="row mt-5">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Previous</button>
                        </div>
                        <div class="col-md-6 text-end" id="nextBtn">
                            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                        </div>
                        <div class="col-md-6 text-end" id="submitBtn" style="display: none;">
                            <input type="submit" class="btn btn-primary" value="Submit" >
                        </div>
                    </div>
                        
                </form>
            </div>
        </div>
    </div>

    <!-- 在body结束标签前添加jQuery和Select2的JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script>

        // 初始化Select2
        $(document).ready(function() {
            $('#bai_no, #rev').select2({
                tags: true,
                placeholder: "Select or type to add new",
                allowClear: true
            });

            // 为所有步骤的输入框添加比较功能
            function checkInputValue(inputElement) {
                const inputValue = parseFloat($(inputElement).val());
                const specElement = $(inputElement).siblings('input[id="spec"]');
                const specValue = specElement.val();
                
                if (!isNaN(inputValue) && specValue) {
                    // 解析规格值
                    const specMatch = specValue.match(/([<>])\s*(\d+)/);
                    if (specMatch) {
                        const operator = specMatch[1];
                        const specNumber = parseFloat(specMatch[2]);
                        
                        let isValid = false;
                        if (operator === '<') {
                            isValid = inputValue < specNumber;
                        } else if (operator === '>') {
                            isValid = inputValue > specNumber;
                        }
                        
                        // 设置颜色
                        if (isValid) {
                            $(inputElement).css('background-color', '#d4edda'); // 绿色
                            $(inputElement).css('border-color', '#c3e6cb');
                        } else {
                            $(inputElement).css('background-color', '#f8d7da'); // 红色
                            $(inputElement).css('border-color', '#f5c6cb');
                        }
                    }
                } else {
                    // 重置颜色
                    $(inputElement).css('background-color', '');
                    $(inputElement).css('border-color', '');
                }
            }

            var total_a = <?php echo $total_a; ?>;
            var total_b = <?php echo $total_b; ?>;

            // 为所有步骤的输入框添加事件监听
            for (let i = 1; i <= total_a; i++) {
                $(`input[name="step${i}[]"]`).on('input', function() {
                    checkInputValue(this);
                });
            }

            // 页面加载时检查所有输入框的值
            for (let i = 1; i <= total_a; i++) {
                $(`input[name="step${i}[]"]`).each(function() {
                    checkInputValue(this);
                });
            }
        });

        let currentMainStep = 1;
        let currentSubStep = 1;
        const totalMainSteps = 5;
        var total_a = <?php echo $total_a; ?>;
        var total_b = <?php echo $total_b; ?>;

        function updateMainStepIndicators() {
            const progress = ((currentMainStep - 1) / (totalMainSteps - 1)) * 100;
            document.querySelector('.progress-bar').style.width = progress + '%';

            document.querySelectorAll('.main-step-dot').forEach((dot, index) => {
                if (index + 1 < currentMainStep) {
                    dot.classList.add('completed');
                    dot.classList.remove('active');
                } else if (index + 1 === currentMainStep) {
                    dot.classList.add('active');
                    dot.classList.remove('completed');
                } else {
                    dot.classList.remove('active', 'completed');
                }
            });
        }

        function updateSubStepIndicators() {
            const subStepIndicator = document.querySelector('.sub-step-indicator');
            if (subStepIndicator) {
                // 在第2步和第3步显示子步骤指示器
                if (currentMainStep === 2 || currentMainStep === 3) {
                    subStepIndicator.style.display = 'flex';
                } else {
                    subStepIndicator.style.display = 'none';
                }
            }

            document.querySelectorAll('.sub-step-dot').forEach((dot, index) => {
                const stepNumber = parseInt(dot.getAttribute('data-sub-step'));
                if (stepNumber < currentSubStep) {
                    dot.classList.add('completed');
                    dot.classList.remove('active');
                } else if (stepNumber === currentSubStep) {
                    dot.classList.add('active');
                    dot.classList.remove('completed');
                } else {
                    dot.classList.remove('active', 'completed');
                }
            });
        }

        function showMainStep(step) {
            document.querySelectorAll('.main-step').forEach(s => {
                s.classList.remove('active');
            });
            document.querySelector(`.main-step[data-step="${step}"]`).classList.add('active');
            updateMainStepIndicators();
            updateSubStepIndicators();
        }

        function showSubStep(step) {
            document.querySelectorAll('.sub-step').forEach(s => {
                s.classList.remove('active');
            });
            const targetStep = document.querySelector(`.sub-step[data-sub-step="${step}"]`);
            if(targetStep) {
                targetStep.classList.add('active');
            }
            updateSubStepIndicators();
        }

        function updateNavigationButtons() {
            document.getElementById('prevBtn').style.display = 
                (currentMainStep === 1) ? 'none' : 'block';
            
            if (currentMainStep === totalMainSteps) {
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('submitBtn').style.display = 'block';
            } else {
                document.getElementById('nextBtn').style.display = 'block';
                document.getElementById('submitBtn').style.display = 'none';
            }
        }

        // 验证子步骤
        function validateSubStep() {
            const currentSubStepElement = document.querySelector(`.sub-step[data-sub-step="${currentSubStep}"]`);
            if (!currentSubStepElement) return true;

            const requiredFields = currentSubStepElement.querySelectorAll('[required]');
            let isValid = true;

            // 清除当前子步骤的错误提示
            currentSubStepElement.querySelectorAll('.error-message').forEach(el => el.remove());

            // 检查所有必填字段
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    // 在label后添加错误提示
                    const label = field.previousElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        const errorSpan = document.createElement('span');
                        errorSpan.className = 'error-message text-danger ms-2';
                        errorSpan.textContent = '* Required';
                        label.appendChild(errorSpan);
                    }
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields before proceeding.');
            }

            return isValid;
        }

        // 验证主步骤
        function validateMainStep() {
            const currentStep = document.querySelector(`.main-step[data-step="${currentMainStep}"]`);
            const requiredFields = currentStep.querySelectorAll('[required]');
            let isValid = true;

            // 清除所有错误提示
            currentStep.querySelectorAll('.error-message').forEach(el => el.remove());

            // 检查所有必填字段
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    // 在label后添加错误提示
                    const label = field.previousElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        const errorSpan = document.createElement('span');
                        errorSpan.className = 'error-message text-danger ms-2';
                        errorSpan.textContent = '* Required';
                        label.appendChild(errorSpan);
                    }
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields before proceeding.');
            }

            return isValid;
        }

        document.querySelector('#nextBtn button').addEventListener('click', () => {
            // 根据当前步骤决定使用哪个验证函数
            let isValid = true;
            if (currentMainStep === 2 || currentMainStep === 3) {
                isValid = validateSubStep();
            } else {
                isValid = validateMainStep();
            }

            if (!isValid) return;

            if (currentMainStep === 2) {
                if (currentSubStep < total_a) {
                    currentSubStep++;
                    showSubStep(currentSubStep);
                } else {
                    currentMainStep++;
                    currentSubStep = total_a + 1;
                    showMainStep(currentMainStep);
                    showSubStep(currentSubStep);
                }
            } else if (currentMainStep === 3) {
                if (currentSubStep < total_a + total_b) {
                    currentSubStep++;
                    showSubStep(currentSubStep);
                } else {
                    currentMainStep++;
                    showMainStep(currentMainStep);
                }
            } else if (currentMainStep < totalMainSteps) {
                currentMainStep++;
                showMainStep(currentMainStep);
            }
            updateNavigationButtons();
        });

        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentMainStep === 3) {
                if (currentSubStep > total_a + 1) {
                    currentSubStep--;
                    showSubStep(currentSubStep);
                } else {
                    currentMainStep--;
                    currentSubStep = total_a;
                    showMainStep(currentMainStep);
                    showSubStep(currentSubStep);
                }
            } else if (currentMainStep === 2) {
                if (currentSubStep > 1) {
                    currentSubStep--;
                    showSubStep(currentSubStep);
                } else {
                    currentMainStep--;
                    showMainStep(currentMainStep);
                }
            } else if (currentMainStep > 1) {
                currentMainStep--;
                showMainStep(currentMainStep);
            }
            updateNavigationButtons();
        });

        let signaturePad;
        
        function initSignaturePad() {
            const canvas = document.getElementById('signature-pad');
            if (!canvas) return;

            canvas.width = 400;
            canvas.height = 200;
            
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                velocityFilterWeight: 0.7,
                minWidth: 0.5,
                maxWidth: 2.5,
                throttle: 16
            });

            // 检查是否存在签名文件
            const signPath = 'assets/sign/<?php echo $_SESSION['username']; ?>.png?v=' + new Date().getTime();
            fetch(signPath)
                .then(response => {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Signature not found');
                })
                .then(blob => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.onload = function() {
                            // 计算缩放比例以适应画布
                            const scale = Math.min(canvas.width / img.width, canvas.height / img.height);
                            const x = (canvas.width - img.width * scale) / 2;
                            const y = (canvas.height - img.height * scale) / 2;
                            
                            // 清除画布
                            signaturePad.clear();
                            
                            // 在画布上绘制缩放后的图片
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
                            
                            // 更新签名数据
                            document.getElementById('signature-input').value = canvas.toDataURL();
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(blob);
                })
                .catch(error => {
                    console.log('No existing signature found');
                });

            const clearButton = document.getElementById('clear-signature');
            if (clearButton) {
                clearButton.addEventListener('click', function() {
                    signaturePad.clear();
                    document.getElementById('signature-input').value = '';
                });
            }

            canvas.addEventListener('mousedown', function(event) {
                event.preventDefault();
            });

            canvas.addEventListener('touchstart', function(event) {
                event.preventDefault();
            });
        }


        // 添加拖放功能
        const dropZone = document.getElementById('drop-zone');
        const logoInput = document.getElementById('logo');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.match('image.*')) {
                handleFile(file);
            } else {
                alert('Please select an image file');
            }
        });

        logoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                handleFile(file);
            }
        });

        function handleFile(file) {
            if (!file.type.match('image.*')) {
                alert('Please select an image file');
                logoInput.value = '';
                document.getElementById('logo-preview').innerHTML = '<div class="text-muted">Preview will appear here</div>';
                return;
            }

            // 创建一个新的FileList对象
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            logoInput.files = dataTransfer.files;

            // 显示预览
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                preview.innerHTML = `<img src="${e.target.result}" alt="Logo Preview">`;
            };
            reader.readAsDataURL(file);
        }

        // 修改清除签名按钮的行为，只清除画板
        document.getElementById('clear-signature')?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            signaturePad.clear();
            document.getElementById('signature-input').value = '';
        });

        // 在表单提交前自动保存签名
        document.querySelector('form').addEventListener('submit', function(e) {
            const signatureInput = document.getElementById('signature-input');
            if ((!signaturePad || signaturePad.isEmpty()) && !signatureInput.value) {
                e.preventDefault();
                alert('Please provide your signature before submitting.');
                return;
            }
            if (signaturePad && !signaturePad.isEmpty()) {
                const signatureData = signaturePad.toDataURL('image/png');
                signatureInput.value = signatureData;
            }
        });

        updateMainStepIndicators();
        updateSubStepIndicators();
        updateNavigationButtons();
    </script>
</body>
</html>


