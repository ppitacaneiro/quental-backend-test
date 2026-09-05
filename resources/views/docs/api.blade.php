<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quental API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.29.0/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.29.0/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: @json(url('/docs/openapi.yaml')),
                dom_id: '#swagger-ui',
                deepLinking: true,
            });
        };
    </script>
</body>
</html>