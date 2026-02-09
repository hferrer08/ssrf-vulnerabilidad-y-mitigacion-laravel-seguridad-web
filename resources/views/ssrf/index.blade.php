<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SSRF Lab</title>
</head>
<body>
    <h1>SSRF Demo</h1>

    <form method="POST" action="/ssrf/vulnerable">
        @csrf
        <label>URL:</label>
        <input type="text" name="url" style="width:400px"
               placeholder="http://example.com">
        <br><br>
        <button type="submit">Probar versión VULNERABLE</button>
    </form>

    <br><hr><br>

    <form method="POST" action="/ssrf/secure">
        @csrf
        <label>URL:</label>
        <input type="text" name="url" style="width:400px">
        <br><br>
        <button type="submit">Probar versión SEGURA</button>
    </form>
</body>
</html>
