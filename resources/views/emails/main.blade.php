<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
         body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .email-wrapper {
            width: 100%;
            padding: 20px 0;
            background-color: #f8f9fa;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 15px ;
            background-color: #007bff;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .header-link{
            color: #ffffff;
            text-decoration: none;
            font-size: 1.2rem;
        }

        .content {
            padding: 20px;
        }
        .content h2 {
            font-size: 22px;
            color: #333;
            margin-top: 0;
        }
        .content p {
            line-height: 1.6;
            margin: 10px 0;
        }
        .content img{
            max-width: 90%;
            height: auto;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #777;
            padding-top: 10px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                <a href="#" class="header-link"> {{ get_setting('title') }} </a>
            </div>
            <div class="content">
                @if($data['title'])
                <h2>{{ $data['title'] }}</h2>
                @endif
                <p>
                {!! $data['message'] !!}
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{get_setting('name')}}. All Rights Reserved.</p>
                <p>
                    Need help? <a href="mailto:{{get_setting('support_email')}}">Contact Support</a>
                </p>

            </div>
        </div>
    </div>
</body>

</html>
