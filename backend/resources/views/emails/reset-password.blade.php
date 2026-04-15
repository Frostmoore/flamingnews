<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ripristina la tua password</title>
</head>
<body style="margin:0;padding:0;background-color:#F8F6F1;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8F6F1;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;">

          <!-- Logo -->
          <tr>
            <td align="center" style="padding-bottom:28px;">
              <span style="font-size:28px;font-weight:900;color:#C41E3A;letter-spacing:-0.5px;">FlamingNews</span>
            </td>
          </tr>

          <!-- Card -->
          <tr>
            <td style="background-color:#ffffff;border:1px solid #E5E7EB;padding:40px 36px;">

              <!-- Icona -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding-bottom:28px;">
                    <div style="width:64px;height:64px;background-color:#FEF2F2;border-radius:50%;display:inline-block;text-align:center;line-height:64px;font-size:30px;">
                      🔑
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Titolo -->
              <h1 style="margin:0 0 12px;font-size:22px;font-weight:800;color:#1A1A1A;text-align:center;line-height:1.3;">
                Ripristina la password
              </h1>

              <!-- Testo -->
              <p style="margin:0 0 8px;font-size:15px;color:#4B5563;text-align:center;line-height:1.6;">
                Hai richiesto di reimpostare la tua password.
              </p>
              <p style="margin:0 0 32px;font-size:14px;color:#6B7280;text-align:center;line-height:1.7;">
                Clicca sul bottone qui sotto per scegliere una nuova password.<br>
                Il link è valido per <strong style="color:#374151;">60 minuti</strong>.
              </p>

              <!-- CTA -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding-bottom:32px;">
                    <a href="{{ $url }}"
                       style="display:inline-block;background-color:#C41E3A;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 36px;letter-spacing:0.3px;">
                      Imposta nuova password →
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Divisore -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="border-top:1px solid #F3F4F6;padding-top:24px;">
                    <p style="margin:0 0 12px;font-size:12px;color:#9CA3AF;text-align:center;">
                      Se non hai richiesto il ripristino della password, ignora questa email.<br>
                      Il tuo account è al sicuro.
                    </p>
                    <p style="margin:0;font-size:11px;color:#9CA3AF;text-align:center;word-break:break-all;">
                      Link diretto: <a href="{{ $url }}" style="color:#C41E3A;text-decoration:none;">{{ $url }}</a>
                    </p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:24px 0 0;">
              <p style="margin:0;font-size:11px;color:#9CA3AF;text-align:center;line-height:1.6;">
                © FlamingNews · Notizie comparate da fonti diverse
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
