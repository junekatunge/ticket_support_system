<?php
// Generate simple PWA icons
function createIcon($size, $filename) {
    $image = imagecreatetruecolor($size, $size);

    // Background color (treasury brown)
    $bgColor = imagecolorallocate($image, 139, 69, 19);
    imagefill($image, 0, 0, $bgColor);

    // Text color (white)
    $textColor = imagecolorallocate($image, 255, 255, 255);

    // Add text "HD" for Helpdesk
    $fontSize = $size / 4;
    $text = "HD";

    // Calculate text position (center)
    $bbox = imagettfbbox($fontSize, 0, __DIR__ . '/path/to/font.ttf', $text);

    // Use imagestring for simpler text rendering
    $text = "HD";
    $fontsize = 5; // Built-in font size
    $textWidth = imagefontwidth($fontsize) * strlen($text);
    $textHeight = imagefontheight($fontsize);
    $x = ($size - $textWidth) / 2;
    $y = ($size - $textHeight) / 2;

    imagestring($image, $fontsize, $x, $y, $text, $textColor);

    // Save image
    imagepng($image, $filename);
    imagedestroy($image);
}

// Generate icons
createIcon(192, __DIR__ . '/pwa-icon-192.png');
createIcon(512, __DIR__ . '/pwa-icon-512.png');

echo "Icons generated successfully!<br>";
echo "<img src='pwa-icon-192.png' width='192'><br>";
echo "<img src='pwa-icon-512.png' width='192'><br>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";
?>
