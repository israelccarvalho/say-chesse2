<?php
// Cria a pasta foto, se não existir
if (!file_exists('foto')) mkdir('foto', 0777, true);

if (isset($_FILES['photo'])) {
    $photo = $_FILES['photo'];
    $filename = 'foto/photo_' . date('Y-m-d_H-i-s') . '.jpg';

    // Salva a foto na pasta foto
    move_uploaded_file($photo['tmp_name'], $filename);
}
?>
