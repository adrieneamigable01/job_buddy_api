<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate of Completion</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cinzel:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* CSS Styles */
    body {
      font-family: 'Open Sans', sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f1f1f1;
    }

    .certificate {
      width: auto;
      height: auto;
      padding: 40px;
      background-color: #fff;
      text-align: center;
      position: relative;
      font-family: 'Cinzel', serif; /* Classic font for certificate */
      color: #3e2a47; /* Dark brown text color */
      border-radius: 10px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
    }

    /* Elegant Ornate Border */
    .certificate::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border: 15px solid #4E3629; /* Elegant border color */
      border-radius: 15px;
      padding: 10px;
      box-sizing: border-box;
      background: url('https://www.transparenttextures.com/patterns/old-wall-2.png') repeat;
    }

    /* Certificate Heading */
    .certificate h1 {
      font-family: 'Great Vibes', cursive;
      font-size: 50px;
      color: #4E3629;
      margin: 0;
      padding: 0;
    }

    .certificate .subheading {
      font-size: 24px;
      color: #3e2a47;
      margin: 20px 0;
    }

    .certificate .recipient {
      font-size: 36px;
      font-weight: bold;
      color: #4E3629;
      margin: 30px 0;
    }

    .certificate .course-name {
      font-size: 22px;
      color: #3e2a47;
      margin: 10px 0;
    }

    .certificate .completion-date {
      font-size: 18px;
      color: #6f4e37;
      margin: 20px 0;
    }

    .certificate .footer {
      position: absolute;
      bottom: 40px;
      left: 40px;
      right: 40px;
      font-size: 18px;
      color: #3e2a47;
    }

    .certificate .signature {
      font-size: 15px;
      color: #4E3629;
      font-style: italic;
    }

    .certificate .seal {
        width: 100px;
        height: 100px;
        /* border: 4px solid #4E3629; */
        /* border-radius: 50%; */
        position: absolute;
        top: 30px;
        right: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .certificate .seal img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        }
        .signature-container {
            display: flex;
            flex-direction: column;
            align-items: center; /* Centers the image and text horizontally */
        }

        .signature-container img {
            margin-bottom: 10px; /* Space between the signature and the name */
        }
  </style>
</head>
<body>

  <div class="certificate">
    <!-- Seal -->
    <div class="seal">
        <?php
            $image_path = FCPATH . 'assets/img/an-color.png';

            // Convert the image to base64
            $image_base64 = base64_encode(file_get_contents($image_path));
            
            // Create the data URI
            $image_data_uri = 'data:image/png;base64,' . $image_base64;
        ?>
        <img src="<?php echo $image_data_uri ?>" alt="Checkmark Seal" />
    </div>

    <!-- Certificate Heading -->
    <h1>Certificate of Completion</h1>

    <!-- Subheading -->
    <div class="subheading">This is to certify that</div>

    <!-- Recipient Name -->
    <div class="recipient"><?php echo $students->full_name ?></div>

    <!-- Course Name -->
    <div class="course-name">Completed the course: 
        <br>
        <h1><?php echo $event->name ?></h1>
    </div>

    <!-- Completion Date -->
    <div class="completion-date">Dated: <?php echo date("F d, Y",strtotime($event->date)) ?></div>

    <div>
        Thank you for demonstrating the type of character and integrity that inspire others
    </div>

    <div style="margin-top:20px;">
        <?php
            $image_path = FCPATH . 'assets/img/icons8-check-100.png';

            // Convert the image to base64
            $image_base64 = base64_encode(file_get_contents($image_path));
            
            // Create the data URI
            $image_data_uri = 'data:image/png;base64,' . $image_base64;
        ?>
        <img src="<?php echo $image_data_uri ?>" alt="Checkmark Seal" />
    </div>

    <!-- Footer -->
    <div class="footer">
        <img style="width: 200px; position:absolute;left:43%;bottom:95%;" src="<?php echo $event_signature->event_host_signature ?>" alt="Host Signature" />
        <span style="text-align: center;text-decoration: underline;"><?php echo $event_signature->event_host ?></span>
        <div class="signature"> <?php echo $event_signature->event_host_role ?></div>
        <div class="date">Date of Issue: January 19, 2025</div>
    </div>
  </div>

</body>
</html>
