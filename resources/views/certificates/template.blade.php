<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate - {{ $student }}</title>
    <style>
        /* Setup Margin Halaman Kertas */
        @page {
            margin: 0cm;
            size: A4 landscape;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333333;
        }

        /* Bingkai Kuning Utama */
        .frame {
            position: absolute;
            top: 40px;
            bottom: 40px;
            left: 40px;
            right: 40px;
            border: 14px solid #FFDE21;
            background-color: #fcfcfc;
            z-index: 1;
        }

        /* Bingkai Tipis di Dalam */
        .inner-frame {
            position: absolute;
            top: 15px;
            bottom: 15px;
            left: 15px;
            right: 15px;
            border: 2px solid #e2e8f0;
            z-index: 2;
            text-align: center;
            padding: 40px 20px;
        }

        /* Tipografi */
        .logo {
            font-size: 15px;
            font-weight: bold;
            color: #FFDE21;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .title {
            font-family: 'Times-Roman', serif;
            font-size: 65px;
            font-weight: normal;
            color: #111827;
            margin: 20px 0 0 0;
            letter-spacing: 12px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 18px;
            color: #64748b;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-top: 5px;
            margin-bottom: 40px;
        }

        .text-muted {
            font-size: 17px;
            color: #64748b;
            margin-bottom: 10px;
        }

        /* Nama Murid */
        .student-name {
            font-family: 'Times-Roman', serif;
            font-size: 60px;
            font-style: italic;
            font-weight: bold;
            color: #0f172a;
            margin: 15px auto;
            border-bottom: 2px solid #FFDE21;
            width: 65%;
            padding-bottom: 15px;
        }

        /* Nama Kursus */
        .course-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
            margin: 25px 0 15px 0;
        }

        .description {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            width: 75%;
            margin: 0 auto;
        }

        /* Layout Footer (Tanda Tangan & Cap) */
        .footer-table {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }

        .footer-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
        }

        .sig-text {
            font-size: 18px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 8px;
        }

        .sig-font {
            font-family: 'Times-Roman', serif;
            font-size: 30px;
            font-style: italic;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .sig-line {
            width: 60%;
            border-top: 1px solid #cbd5e1;
            margin: 0 auto;
            padding-top: 8px;
        }

        .sig-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        /* Stempel Bulat Anti-Gagal */
        .seal {
            width: 110px;
            height: 110px;
            background-color: #FFDE21;
            border-radius: 50%;
            margin: 0 auto;
            border: 5px solid #ffffff;
            box-shadow: 0 0 0 2px #FFDE21;
            position: relative;
        }

        .seal-text {
            position: absolute;
            top: 32px;
            left: 0;
            width: 100%;
            font-size: 11px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1.4;
            letter-spacing: 1px;
        }

        /* ID Sertifikat */
        .cert-id {
            position: absolute;
            bottom: 25px;
            left: 45px;
            font-size: 11px;
            color: #94a3b8;
            font-family: 'Courier', monospace;
            z-index: 10;
        }
    </style>
</head>
<body>

    <div class="frame">
        <div class="inner-frame">
            
            <div class="logo">- ASPAS LEARNING -</div>
            
            <h1 class="title">Certificate</h1>
            <div class="subtitle">Of Completion</div>

            <div class="text-muted">This is to certify that</div>
            
            <div class="student-name">{{ $student }}</div>

            <div class="text-muted" style="margin-top: 40px;">Has successfully completed the final project for the course</div>
            
            <div class="course-title">{{ $course }}</div>

            <div class="description">
                Demonstrating outstanding dedication, technical proficiency, and successful mastery of the curriculum provided by Aspas LMS.
            </div>

            <table class="footer-table">
                <tr>
                    <td>
                        <div class="sig-text">{{ $date }}</div>
                        <div class="sig-line"></div>
                        <div class="sig-label">Date of Completion</div>
                    </td>
                    
                    <td>
                        <div class="seal">
                            <div class="seal-text">ASPAS<br>OFFICIAL<br>SEAL</div>
                        </div>
                    </td>
                    
                    <td>
                        <div class="sig-font">Jonathan Aspas</div>
                        <div class="sig-line"></div>
                        <div class="sig-label">Instructor Signature</div>
                    </td>
                </tr>
            </table>

        </div>
        
        <div class="cert-id">
            ID: ASP-{{ strtoupper(substr(md5($student.$course), 0, 8)) }}-{{ date('Y') }}
        </div>
    </div>

</body>
</html>