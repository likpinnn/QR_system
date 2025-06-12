<?php
include_once('db.php');
// if(!isset($_POST['report-name'])) {
//     header('Location: index.php');
//     exit();
// }

// print_r($_FILES['logo']);

// 处理logo
if (isset($_FILES['logo'])) {
    if(($_FILES['logo']) == null){
        // 检查上传错误
        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            die('Logo upload error: ' . $_FILES['logo']['error']);
        }

        // 确保目录存在
        $logo_dir = 'assets/img';
        if (!file_exists($logo_dir)) {
            if (!mkdir($logo_dir, 0777, true)) {
                die('Failed to create logo directory');
            }
        }

        // 获取文件信息
        $file = $_FILES['logo'];
        
        $filename = $_SESSION['username'] . '.png';
        $filepath = $logo_dir . '/' . $filename;

        // 移动文件
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            die('Failed to save logo file. Error: ' . error_get_last()['message']);
        }
    }
    
}


// 处理签名
if (isset($_POST['signature']) && !empty($_POST['signature'])) {
    // 确保目录存在
    $sign_dir = 'assets/sign';
    if (!file_exists($sign_dir)) {
        mkdir($sign_dir, 0777, true);
    }

    // 获取签名数据
    $signature_data = $_POST['signature'];
    $signature_data = str_replace('data:image/png;base64,', '', $signature_data);
    $signature_data = base64_decode($signature_data);

    // 保存签名文件
    $filename = $_SESSION['username'] . '.png';
    $filepath = $sign_dir . '/' . $filename;
    file_put_contents($filepath, $signature_data);
}   


//step 1
$logo = 'assets/img/'.$_SESSION['username'] . '.png';
$report_name1 = $_POST['report-name1'];
$report_name2 = $_POST['report-name2'];
$tools = $_POST['tools'];
$reference_document = $_POST['reference-document'];
$bai_no = $_POST['bai_no'];
$rev = $_POST['rev'];
$serial_number = $_POST['serial-number'];
$date_completed = $_POST['date-completed'];
$pcb_part_number = $_POST['pcb-part-number'];
$pcb_serial_number = $_POST['pcb-serial-number'];

//step 2
$count_a = "SELECT * FROM `report` WHERE `type` = 'a' GROUP BY `step`";
$count_a_result = $conn->query($count_a);
$count_a_data = $count_a_result->fetchAll(PDO::FETCH_ASSOC);
$total_a = count($count_a_data);

$cc = [];
$cc2 = [];
for($i=1; $i<=$total_a; $i++){
    if($i <= 5){
        $cc[] = $_POST['step'.$i];
    }else{
        $cc2[] = $_POST['step'.$i];
    }
}


//step 3
$count_b = "SELECT * FROM `report` WHERE `type` = 'b' GROUP BY `step`";
$count_b_result = $conn->query($count_b);
$count_b_data = $count_b_result->fetchAll(PDO::FETCH_ASSOC);
$total_b = count($count_b_data);

$final =[];
for($i=1; $i<=$total_b; $i++){
    $final[] = $_POST['step'.($i+$total_a)];
}


//step 4
$revision_sql = "SELECT * FROM `revision_history` ORDER BY `Rev` ASC";
$revision_result = $conn->query($revision_sql);
$revision_data = $revision_result->fetchAll(PDO::FETCH_ASSOC);

//step 5
$name = $_POST['name'];
$sign = 'assets/sign/'.$_SESSION['username'] . '.png?v=' . time();
$date = $_POST['date'];

?>

<!DOCTYPE html>
<html>
<?php include_once 'header.php'; ?>
<head>
    <meta charset="UTF-8">
    <title>QR Report System</title>
    <!-- 添加html2pdf.js库 -->
    <script src="assets/js/html2pdf.bundle.min.js"></script>
    <style>
        .A4 {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 20mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
            box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);
            margin-top: 20px;
            position: relative;
            page-break-after: always;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
        }
        .export-btn {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .back-btn {
            position: fixed;
            top: 150px;
            right: 20px;
            z-index: 1000;
            padding: 10px 45px;
            background-color:rgb(143, 143, 143);
            color: white;
            border: none;
            border-radius: 5px;
        }
        .print-btn{
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            padding: 10px 45px;
            background-color:rgb(70, 178, 79);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: none;
        }
        .submit-btn{
            position: fixed;
            top: 200px;
            right: 20px;
            z-index: 1000;
            padding: 10px 35px;
            background-color:rgb(70, 178, 79);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .export-btn:hover {
            background-color: #0056b3;
        }
        .footer {
            position: relative;
            margin-top: auto;
            background: white;
            border-top: 1px solid #000;
            padding: 5mm 0;
            font-size: 9px;
            box-sizing: border-box;
        }
        
        .page-number {
            float: right;
        }
        @media print {
            .A4 {
                page-break-after: always;
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 10mm 20mm;  /* 减小打印时的上下内边距 */
                box-shadow: none;
            }
            .export-btn {
                display: none;
            }
        }
        .content-section {
            page-break-inside: avoid;
            position: relative;
        }
        .header-section {
            margin-top: 0;  /* 移除顶部边距 */
            margin-bottom: 5mm;  /* 减小底部边距 */
            position: relative;
            top: 0;
        }
        .header-section table {
            margin-bottom: 0;  /* 移除表格底部边距 */
        }
        .header-section img {
            max-height: 40px;  /* 限制 logo 高度 */
            width: auto;
        }
        .header-section h6 {
            margin: 0;  /* 移除标题边距 */
            line-height: 1.2;  /* 减小行高 */
        }
        .row {
            margin: 0;
            padding: 0;
        }

        table,th,td{
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 13px;
        }
        td,th{
            padding: 5px;
        }
        table{
            width: 100%;
        }

        .content-wrapper {
            flex: 1;
            position: relative;
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <button class="export-btn" id="export-btn" onclick="exportToPDF()">Save to PDF</button>
    <button class="print-btn" id="print-btn" onclick="printContent()">Print</button>
    <button class="back-btn" onclick="handleBack()" >Back</button>
    <input type="submit" value="Update" class="submit-btn">
    <div id="content-container">
        <form action="" method="post">
            <div class="A4">
                <div class="row">
                    <!-- Header Section -->
                    <div class="col-md-12 content-section header-section">
                        <table class="table table-bordered border-dark">
                            <tr class="row text-center border-dark border-2 border">
                                <td class="col-md-3">
                                    <img id="logoPreview" src="<?php echo $logo; ?>" alt="Logo" style="max-height: 40px; max-width: 100%; cursor: pointer;" onclick="document.getElementById('logoInput').click()">
                                    <input type="file" id="logoInput" accept="image/*" style="display: none;">
                                </td>
                                <td class="col-md-6">
                                    <input type="text" name="report-name1" class="form-control text-center p-0 m-0" style="font-weight:bold; font-size: 18px; border: 0px;" value="<?=$report_name1?>">
                                    <input type="text" name="report-name2" class="form-control text-center p-0 m-0" style="font-weight:bold; font-size: 18px; border: 0px;" value="<?=$report_name2?>">
                                </td>
                                <td class="col-md-3">
                                    <input type="text" name="bai_no" id="bai_no" value="<?=$bai_no?>" class="form-control w-100 text-center p-0 m-0" style="font-weight: bold; font-size: 18px; border: 0px" onchange="updateBaiNo()">  
                                    <input type="text" name="rev" id="rev" value="<?=$rev?>" class="form-control w-100 text-center p-0 m-0" style="font-weight: bold; font-size: 18px; border: 0px" onchange="updateRev()">  
                                </td>
                            </tr>              
                        </table>
                    </div>

                    <!-- Technical Information Section -->
                    <div class="col-md-12 content-section mb-4">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-primary">Technical Information</h5>
                            </div>
                            <div class="col-md-12 px-5">
                                <table>
                                    <tr>
                                        <td style="width: 20%;">Tools</td>
                                        <td style="width: 80%;">
                                            <input type="text" name="tools" value="<?=$tools?>" class="border-0 form-control p-0 m-0" style="font-size: 13px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 20%;">Reference Document</td>
                                        <td style="width: 80%;">
                                            <input type="text" name="reference-document" value="<?=$reference_document?>" class="border-0 form-control p-0 m-0" style="font-size: 13px;">
                                        </td>
                                        
                                    </tr>                           
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Manufacturing Reference Section -->
                    <div class="col-md-12 content-section mb-2">
                        <span style="font-size: 12px;">For manufaturing reference only:</span>
                        <table>
                            <tr>
                                <td>Brooks P/N and Revision</td>
                                <td>
                                    <input type="text" id="bai_no_display" value="<?=$bai_no?>" class="border-0 form-control p-0 m-0" style="font-size: 13px;" onchange="updateBaiNoFromBottom()">
                                </td>
                                <td>
                                    <label>REV</label>
                                    <input type="text" id="rev_display" value="<?=$rev?>" class="border-0 p-0 m-0" style="font-size: 13px;" onchange="updateRevFromBottom()">
                                </td>
                            </tr>   
                            <tr>
                                <td>Brooks's Serial Number</td>
                                <td colspan="2">
                                    <input type="text" name="serial-number" value="<?=$serial_number?>" class="form-control border-0 p-0 m-0" style="font-size: 13px;">
                                </td>
                            </tr>
                            <tr>
                                <td>Date Completed</td>
                                <td colspan="2">
                                    <input type="text" name="date-completed" value="<?=$date_completed?>" class="form-control border-0 p-0 m-0" style="font-size: 13px;">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- PCB Information Section -->
                    <div class="col-md-12 content-section mb-4 mt-3">
                        <table>
                            <tr>
                                <td></td>
                                <td>Brookss Part Number</td>
                                <td>Supplier Serial Number</td>
                            </tr>
                            <tr>
                                <td>Carriage PCB</td>
                                <td>
                                    <input type="text" name="pcb-part-number" value="<?=$pcb_part_number?>" class="form-control border-0 p-0 m-0" style="font-size: 13px;">
                                </td>
                                <td>
                                    <input type="text" name="pcb-serial-number" value="<?=$pcb_serial_number?>" class="form-control border-0 p-0 m-0" style="font-size: 13px;">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Continuity Check Section -->
                    <div class="col-md-12 content-section">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-primary">Contunuity Check</h5>
                            </div>
                            <div class="col-md-12">
                                <table>
                                    <tr class="text-center">
                                        <th class="bg-primary text-white" style="width: 10%;">Step</th>
                                        <th class="bg-primary text-white" style="width: 50%;">Action</th>
                                        <th class="bg-primary text-white" style="width: 15%;">Spec</th>
                                        <th class="bg-primary text-white" style="width: 10%;">Result</th>
                                        <th class="bg-primary text-white" style="width: 10%;">Pass/Fail</th>
                                    </tr>
                                    <?php 
                                    $x = 0;
                                    $current_step = 0;
                                    $rowspan_count = 0;
                                    $first_row = true;
                                    
                                    foreach($cc as $step){
                                        $x++;
                                        $rowspan_count = count($step);
                                        $first_row = true;
                                        
                                        // 获取当前步骤的所有actions
                                        $report_sql = "SELECT * FROM `report` WHERE `step` = '$x' AND `type` = 'a'";
                                        $report_result = $conn->query($report_sql);
                                        $report_data = $report_result->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        // 遍历每个action
                                        foreach($report_data as $index => $report){
                                            $action = $report['action'];
                                            $spec = $report['spec'];
                                            $result = isset($step[$index]) ? $step[$index] : '';
                                    ?>
                                    <tr>
                                        <?php if($first_row) { ?>
                                            <th rowspan="<?php echo $rowspan_count; ?>" class="text-center"><?=$x?>.</th>
                                        <?php } ?>
                                        <td><?=$action?></td>
                                        <td style="text-align: center;"><?=$spec?> Ohm</td>
                                        <td class="text-center">
                                            <input type="text" class="form-control border-0 p-0 m-0 text-center" style="font-size: 13px;" value="<?=$result?>" onchange="checkResult(this, '<?=$spec?>')">
                                        </td>
                                        <td class="text-center result-cell" data-spec="<?=$spec?>">
                                            <?php 
                                            // 移除所有空格以便比较
                                            $result = trim($result);
                                            $spec = trim($spec);
                                            
                                            // 检查是否包含比较运算符
                                            if (preg_match('/^([<>=]+)(.+)$/', $spec, $matches)) {
                                                $operator = $matches[1];
                                                $spec_value = trim($matches[2]);
                                                
                                                // 将结果和规格值转换为数值进行比较
                                                $result_num = floatval($result);
                                                $spec_num = floatval($spec_value);
                                                
                                                switch($operator) {
                                                    case '>':
                                                        echo $result_num > $spec_num ? 'Pass' : 'Fail';
                                                        break;
                                                    case '>=':
                                                        echo $result_num >= $spec_num ? 'Pass' : 'Fail';
                                                        break;
                                                    case '<':
                                                        echo $result_num < $spec_num ? 'Pass' : 'Fail';
                                                        break;
                                                    case '<=':
                                                        echo $result_num <= $spec_num ? 'Pass' : 'Fail';
                                                        break;
                                                    case '=':
                                                    case '==':
                                                        echo $result_num == $spec_num ? 'Pass' : 'Fail';
                                                        break;
                                                    default:
                                                        echo 'Fail';
                                                }
                                            } else {
                                                // 如果没有比较运算符，直接比较字符串
                                                echo strtolower($result) === strtolower($spec) ? 'Pass' : 'Fail';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php 
                                            $first_row = false;
                                        }
                                    } 
                                    ?>
                                </table> 
                            </div>
                        </div>
                    </div>
                    <?php if(count($cc2) > 0){ ?>
                        <div class="col-md-12 content-section">
                            <div class="row">
                                <div class="col-md-12">
                                <table>
                                        <tr class="text-center">
                                            <th class="bg-primary text-white" style="width: 10%;">Step</th>
                                            <th class="bg-primary text-white" style="width: 50%;">Action</th>
                                            <th class="bg-primary text-white" style="width: 15%;">Spec</th>
                                            <th class="bg-primary text-white" style="width: 10%;">Result</th>
                                            <th class="bg-primary text-white" style="width: 10%;">Pass/Fail</th>
                                        </tr>
                                        <?php 
                                        $x = 5;
                                        $current_step = 0;
                                        $rowspan_count = 0;
                                        $first_row = true;
                                        
                                        foreach($cc2 as $step){
                                            $x++;
                                            $rowspan_count = count($step);
                                            $first_row = true;
                                            
                                            // 获取当前步骤的所有actions
                                            $report_sql = "SELECT * FROM `report` WHERE `step` = '$x' AND `type` = 'a'";
                                            $report_result = $conn->query($report_sql);
                                            $report_data = $report_result->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            // 遍历每个action
                                            foreach($report_data as $index => $report){
                                                $action = $report['action'];
                                                $spec = $report['spec'];
                                                $result = isset($step[$index]) ? $step[$index] : '';
                                        ?>
                                        <tr>
                                            <?php if($first_row) { ?>
                                                <th rowspan="<?php echo $rowspan_count; ?>" class="text-center"><?=$x?>.</th>
                                            <?php } ?>
                                            <td><?=$action?></td>
                                            <td class="text-center"><?=$spec?> Ohm</td>
                                            <td class="text-center">
                                                <input type="text" class="form-control border-0 p-0 m-0 text-center" style="font-size: 13px;" value="<?=$result?>" onchange="checkResult(this, '<?=$spec?>')">
                                            </td>
                                            <td class="text-center result-cell" data-spec="<?=$spec?>">
                                                <?php 
                                                // 移除所有空格以便比较
                                                $result = trim($result);
                                                $spec = trim($spec);
                                                
                                                // 检查是否包含比较运算符
                                                if (preg_match('/^([<>=]+)(.+)$/', $spec, $matches)) {
                                                    $operator = $matches[1];
                                                    $spec_value = trim($matches[2]);
                                                    
                                                    // 将结果和规格值转换为数值进行比较
                                                    $result_num = floatval($result);
                                                    $spec_num = floatval($spec_value);
                                                    
                                                    switch($operator) {
                                                        case '>':
                                                            echo $result_num > $spec_num ? 'Pass' : 'Fail';
                                                            break;
                                                        case '>=':
                                                            echo $result_num >= $spec_num ? 'Pass' : 'Fail';
                                                            break;
                                                        case '<':
                                                            echo $result_num < $spec_num ? 'Pass' : 'Fail';
                                                            break;
                                                        case '<=':
                                                            echo $result_num <= $spec_num ? 'Pass' : 'Fail';
                                                            break;
                                                        case '=':
                                                        case '==':
                                                            echo $result_num == $spec_num ? 'Pass' : 'Fail';
                                                            break;
                                                        default:
                                                            echo 'Fail';
                                                    }
                                                } else {
                                                    // 如果没有比较运算符，直接比较字符串
                                                    echo strtolower($result) === strtolower($spec) ? 'Pass' : 'Fail';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php 
                                                $first_row = false;
                                            }
                                        } 
                                        ?>
                                    </table> 
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <!-- Final Verification Section -->
                    <div class="col-md-12 content-section">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-primary">Final Verification</h5>
                                <table>
                                    <tr class="text-center">
                                        <th class="bg-primary text-white" style="width: 10%;">Step</th>
                                        <th class="bg-primary text-white" style="width: 80%;">Action</th>
                                        <th class="bg-primary text-white" style="width: 10%;">Pass/Fail</th>
                                    </tr>
                                    <?php
                                    $y = 0;
                                    foreach($final as $step){
                                        $y++;
                                        $rowspan_count = count($step);
                                        $first_row = true;
                                        foreach($step as $s){
                                            $report_sql = "SELECT * FROM `report` WHERE `step` = '$y' AND `type` = 'b'";
                                            $report_result = $conn->query($report_sql);
                                            $report_data = $report_result->fetchAll(PDO::FETCH_ASSOC);
                                            foreach($report_data as $index => $report){
                                                $action = $report['action'];
                                                $spec = $report['spec'];
                                                $result = $s;
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center"><?=$y+$total_a?></td>
                                                <td><?=$action?></td>
                                                <td class="text-center">
                                                    <select class="form-control pass-fail-select border-0 bg-transparent" onchange="updatePassFailColor(this)" style="border: none; text-align: center; font-size: 13px;">
                                                        <option value="Pass" <?php echo ($result == 'Pass') ? 'selected' : ''; ?>>Pass</option>
                                                        <option value="Fail" <?php echo ($result == 'Fail') ? 'selected' : ''; ?>>Fail</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }

                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div class="col-md-12 content-section" style="margin-top: 50px;">
                        <div class="row">
                            <div class="col-md-12 px-5">
                                <div class="row">
                                    <table>
                                        <tr>
                                            <td class="col-md-10 text-center">
                                                <img src="<?=$sign?>" alt="Signature" style="max-width: 30%; max-height: auto;">
                                            </td>
                                            <td class="col-md-2 align-middle text-center">
                                                <?php echo $date;?>
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="col-md-10" >
                                        <span style="font-size: 12px;">Completion signature: <?=$name?></span>
                                    </div>
                                    <div class="col-md-2" >
                                        <span style="font-size: 12px;">Date</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revision History Section -->
                    <div class="col-md-12 content-section" style="margin-top: 50px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-primary">Revision History</h5>
                                <table>
                                    <tr>
                                        <th class="bg-primary text-white" style="width: 3%;">Rev</th>
                                        <th class="bg-primary text-white" style="width: 15%;">ECO</th>
                                        <th class="bg-primary text-white" style="width: 16%;">Date</th>
                                        <th class="bg-primary text-white" style="width: 50%;">Action</th>
                                        <th class="bg-primary text-white" style="width: 15%;">Author</th>
                                    </tr>
                                    <?php
                                    foreach($revision_data as $revision){
                                        ?>
                                        <tr>
                                            <td><?=$revision['Rev']?></td>
                                            <td><?=$revision['ECO']?></td>
                                            <td><?=$revision['date']?></td>
                                            <td><?=$revision['action']?></td>
                                            <td><?=$revision['Author']?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <div class="row">
                        <div class="col-md-12">
                            <div>Proprietary information - This document and the information disclosed herein is confidential and proprietary to Brooks Automation and may not be reproduced in whole or in part or disclosed to any third party or used without the prior written consent of Brooks Automation, Inc. </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 text-primary">
                                    www.brooks.com
                                </div>
                                <div class="col-md-12 text-primary">
                                    +1-800-FOR-GUTS (1-800-367-4887)
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="page-number">Page 1</div>
                                </div>
                                <div class="col-md-12 text-end">
                                    <?=$bai_no.' REV '.$rev?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
    </div>

    <script>
        let isPdfSaved = false;  // 添加变量跟踪PDF保存状态
        let savedPdfPath = '';   // 添加变量存储保存的PDF路径

        function uniqueId() {
            return Math.random().toString(36).substr(2, 9);
        }

        function printContent() {
            if (savedPdfPath) {
                window.open(savedPdfPath, '_blank');
            } else {
                alert('请先保存 PDF 文件');
            }
        }

        function exportToPDF() {
            const unique_id = uniqueId();
            const element = document.getElementById('content-container');
            const timestamp = new Date().getTime();
            const filename = '<?=$bai_no.'_'.$rev.'_'.$date?>_' + unique_id + '.pdf';
            
            // 获取总页数
            const totalPages = document.querySelectorAll('.A4').length;
            console.log(totalPages);

            const opt = {
                margin: 0,
                filename: filename,
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    logging: true,
                    allowTaint: true,
                    scrollY: 0
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait'
                },
                pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
            };

            // 等待所有图片加载完成
            const images = element.getElementsByTagName('img');
            const imagePromises = Array.from(images).map(img => {
                if (img.complete) {
                    return Promise.resolve();
                }
                return new Promise(resolve => {
                    img.onload = resolve;
                    img.onerror = resolve;
                });
            });

            Promise.all(imagePromises).then(() => {
                // 生成PDF
                html2pdf().set(opt).from(element).outputPdf('blob').then(pdfBlob => {
                    // 创建FormData对象
                    const formData = new FormData();
                    formData.append('pdf', pdfBlob, filename);
                    formData.append('filename', filename);

                    // 发送到服务器
                    fetch('save_pdf.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            isPdfSaved = true;  // 设置保存状态为true
                            savedPdfPath = data.path;  // 保存PDF路径
                            document.getElementById('export-btn').style.display = 'none';
                            document.getElementById('print-btn').style.display = 'block';
                            alert('PDF is saved');
                        } else {
                            console.error('Error:', data);
                            alert('Save PDF error:' + data.message + '\nDebug info:' + JSON.stringify(data.debug, null, 2));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Save PDF error:' + error.message);
                    });
                });

            });

            // 导出完成后，恢复所有页面的显示
            setTimeout(() => {
                pages.forEach(page => {
                    page.style.display = '';
                });
            }, 1000);
        }

        function handleBack() {
            if (!isPdfSaved) {
                if (confirm('You have not saved the PDF. Do you want to save before leaving?')) {
                    window.location.href = 'index.php';
                }
            } else {
                window.location.href = 'index.php';
            }
        }

        function splitContentIntoPages() {
            const A4_HEIGHT = 295; // A4高度（mm）
            const MARGIN = 10; // 上下边距（mm）
            const FOOTER_HEIGHT = 40; // footer高度（mm）
            const MAX_CONTENT_HEIGHT = A4_HEIGHT - MARGIN - FOOTER_HEIGHT - 10; // 最大内容高度，额外减去10mm作为安全边距

            const container = document.getElementById('content-container');
            const sections = container.querySelectorAll('.content-section');
            let currentPage = container.querySelector('.A4');
            let currentHeight = 0;
            let pageCount = 1;

            // 清除所有现有的A4页面，只保留第一个
            const allPages = container.querySelectorAll('.A4');
            for (let i = 1; i < allPages.length; i++) {
                allPages[i].remove();
            }

            sections.forEach(section => {
                const sectionHeight = section.offsetHeight * 0.264583333; // 将px转换为mm
                
                if (currentHeight + sectionHeight > MAX_CONTENT_HEIGHT) {
                    // 创建新页面
                    pageCount++;
                    const newPage = document.createElement('div');
                    newPage.className = 'A4';
                    newPage.innerHTML = `
                        <div class="content-wrapper">
                            <div class="row"></div>
                            <div class="footer">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>Proprietary information - This document and the information disclosed herein is confidential and proprietary to Brooks Automation and may not be reproduced in whole or in part or disclosed to any third party or used without the prior written consent of Brooks Automation, Inc. </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 text-primary">
                                                www.brooks.com
                                            </div>
                                            <div class="col-md-12 text-primary">
                                                +1-800-FOR-GUTS (1-800-367-4887)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="page-number">Page ${pageCount}</div>
                                            </div>
                                            <div class="col-md-12 text-end">
                                                <?=$bai_no.' REV '.$rev?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    `;
                    container.appendChild(newPage);
                    currentPage = newPage;
                    currentHeight = 0;
                }

                // 将section移动到当前页面
                currentPage.querySelector('.row').appendChild(section);
                currentHeight += sectionHeight;
            });
        }

        // 页面加载完成后执行分页
        window.addEventListener('load', splitContentIntoPages);

        // 添加Logo预览功能
        document.getElementById('logoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // 添加更新bai_no和rev的函数
        function updateBaiNo() {
            const baiNo = document.getElementById('bai_no').value;
            document.getElementById('bai_no_display').value = baiNo;
        }

        function updateRev() {
            const rev = document.getElementById('rev').value;
            document.getElementById('rev_display').value = rev;
        }

        function updateBaiNoFromBottom() {
            const baiNo = document.getElementById('bai_no_display').value;
            document.getElementById('bai_no').value = baiNo;
        }

        function updateRevFromBottom() {
            const rev = document.getElementById('rev_display').value;
            document.getElementById('rev').value = rev;
        }

        // 添加页面加载完成后的初始化函数
        window.addEventListener('load', function() {
            // 获取所有结果输入框
            const resultInputs = document.querySelectorAll('input[onchange^="checkResult"]');
            // 对每个输入框执行一次检查
            resultInputs.forEach(input => {
                const spec = input.getAttribute('onchange').match(/'([^']+)'/)[1];
                checkResult(input, spec);
            });
        });

        function checkResult(input, spec) {
            const result = input.value.trim();
            const specValue = spec.trim();
            const resultCell = input.parentElement.nextElementSibling;
            
            // 检查是否包含比较运算符
            const matches = specValue.match(/^([<>=]+)(.+)$/);
            if (matches) {
                const operator = matches[1];
                const specNum = parseFloat(matches[2]);
                const resultNum = parseFloat(result);
                
                let isPass = false;
                switch(operator) {
                    case '>':
                        isPass = resultNum > specNum;
                        break;
                    case '>=':
                        isPass = resultNum >= specNum;
                        break;
                    case '<':
                        isPass = resultNum < specNum;
                        break;
                    case '<=':
                        isPass = resultNum <= specNum;
                        break;
                    case '=':
                    case '==':
                        isPass = resultNum == specNum;
                        break;
                    default:
                        isPass = false;
                }
                
                resultCell.textContent = isPass ? 'Pass' : 'Fail';
                resultCell.style.backgroundColor = isPass ? '#90EE90' : '#FFB6C1';
            } else {
                // 如果没有比较运算符，直接比较字符串
                const isPass = result.toLowerCase() === specValue.toLowerCase();
                resultCell.textContent = isPass ? 'Pass' : 'Fail';
                resultCell.style.backgroundColor = isPass ? '#90EE90' : '#FFB6C1';
            }
        }

        function updatePassFailColor(selectElement) {
            const td = selectElement.parentElement;
            if (selectElement.value === 'Pass') {
                td.style.backgroundColor = '#90EE90';
            } else {
                td.style.backgroundColor = '#FFB6C1';
            }
        }

        // 页面加载时初始化所有下拉框的颜色
        window.addEventListener('load', function() {
            const selects = document.querySelectorAll('.pass-fail-select');
            selects.forEach(select => {
                updatePassFailColor(select);
            });
        });
    </script>
</body>
</html> 