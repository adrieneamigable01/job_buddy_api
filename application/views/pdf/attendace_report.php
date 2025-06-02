<?php
    if($data['_isError']){
        echo "Error Please contact a support!";exit; 
    }
    // print_r($data);return false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1, h2 {
            text-align: center;
        }

        .report-header {
            display: flex;
            justify-content: space-between; /* Ensures space between the columns */
            margin-bottom: 20px;
        }

        .left-column, .right-column {
            display: inline-block;
        }

        .left-column {
            width: 60%; /* The left side takes more space */
        }

        .right-column {
            width: 35%; /* The right side takes less space */
            text-align: right; /* Align text to the right */
        }

        .report-header p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .table-title {
            font-size: 18px;
            margin-top: 30px;
            text-align: center;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px; /* Space between logo and title */
        }

        .logo-container img {
            max-width: 150px; /* Adjust the size of the logo */
            height: auto;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <!-- Replace the 'logo.png' with the correct path to your logo image -->
        <!-- <img src="<?php echo FCPATH . 'assets/img/an.png' ?>" alt="Logo"> -->
    </div>

    <h1>Attendance Report</h1>
    <br>
    <div class="report-header">
        <div class="left-column">
            <p><strong>Event Name:</strong> <?php echo $data['event']->name?></p>
            <p><strong>Event Date:</strong> <?php echo $data['event']->date?></p>
            <p><strong>Event Time:</strong> <?php echo $data['event']->start_time ?> - <?php echo $data['event']->end_time?></p>
            <p><strong>College:</strong> <?php echo $data['college']->college??'N/A'?></p>
            <p><strong>Program:</strong> <?php echo $data['program']->program??'N/A'?></p>
            <p><strong>Year Level:</strong> <?php echo $data['year_level']->year_level??'N/A'?></p>
            <p><strong>Section:</strong> <?php echo $data['section']->section??'N/A'?></p>
        </div>
        
        <!-- Right Column: Export Details -->
        <div class="right-column">
            <p><strong>Export Date:</strong> <?php echo date("F d Y") ?></p>
            <p><strong>Export Time:</strong> <?php echo date("H:i:s") ?> </p>
        </div>
    </div>

    <div class="table-title">
        <h2>Student Attendance</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Time-In</th>
                <th>Time-Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($data['attendance']['students'] as $key => $value) {
                    echo "  <tr>
                        <td>".$value['id']."</td>
                        <td>".$value['name']."</td>
                        <td>".($value['time_in'] == "" ? "--:--" : $value['time_in'])."</td>
                        <td>".($value['time_out'] == "" ? "--:--" : $value['time_out'])."</td>
                        <td>".$value['status']."</td>
                    </tr>";
                }
            ?>
         
        </tbody>
    </table>

    <div class="table-title">
        <h2>Teacher Attendance</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Time-In</th>
                <th>Time-Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
            foreach ($data['attendance']['teachers'] as $key => $value) {
                    echo "  <tr>
                        <td>".$value['id']."</td>
                        <td>".$value['name']."</td>
                        <td>".($value['time_in'] == "" ? "--:--" : $value['time_in'])."</td>
                        <td>".($value['time_out'] == "" ? "--:--" : $value['time_out'])."</td>
                        <td>".$value['status']."</td>
                    </tr>";
                }
            ?>
        </tbody>
    </table>

</body>
</html>
