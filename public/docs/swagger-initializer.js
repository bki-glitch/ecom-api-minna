window.onload = function() {
  fetch('./env.php')
    .then(response => response.json())
    .then(env => {
      var baseUrl = env.FOLDER_PATH || '/car-ceylon-api';
      if (!baseUrl.startsWith('/')) baseUrl = '/' + baseUrl;
      baseUrl = baseUrl.replace(/\/+$/, ''); // Remove trailing slash
      window.ui = SwaggerUIBundle({
        url: './swagger.php',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout"
      });
    });
};
