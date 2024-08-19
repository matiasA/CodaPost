# CodaPost: Generador de Noticias con CrewAI

CodaPost es un proyecto que combina un plugin de WordPress para la generación automática de contenido con una API backend potenciada por CrewAI y OpenAI.

## Características

- Plugin de WordPress para la generación y publicación automática de artículos
- API RESTful construida con FastAPI para la generación de contenido
- Uso de CrewAI para orquestar agentes de IA (investigador, escritor, editor)
- Integración con DALL-E para la generación de imágenes relacionadas con el contenido
- Búsqueda en tiempo real de información utilizando SerperDevTool
- Opciones personalizables para la estructura, estilo de escritura y longitud del artículo
- Logging detallado del proceso de generación


## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Python 3.12 o superior
- Clave API de OpenAI
- Clave API de Serper

## Instalación

### Plugin de WordPress

1. Descarga el contenido de la carpeta `/CodaPost` y colócalo en el directorio `/wp-content/plugins/` de tu instalación de WordPress.
2. Activa el plugin "CodaPost" desde el panel de administración de WordPress.

### Backend

1. Navega al directorio `/Backend`:
   ```
   cd Backend
   ```
2. Crea un entorno virtual:
   ```
   python -m venv venv
   ```
3. Activa el entorno virtual:
   - En Windows: `venv\Scripts\activate`
   - En macOS y Linux: `source venv/bin/activate`
4. Instala las dependencias:
   ```
   pip install -r requirements.txt
   ```

## Configuración

1. Crea un archivo `.env` en el directorio `/Backend/app` con las siguientes variables:
   ```
   OPENAI_API_KEY=tu_clave_api_de_openai
   SERPER_API_KEY=tu_clave_api_de_serper
   ```
2. Configura la URL del backend en el plugin de WordPress desde el panel de administración.

## Uso

1. Inicia el servidor FastAPI:
   ```
   cd Backend
   uvicorn app.main:app --reload
   ```
2. Utiliza el panel de administración de WordPress para generar y publicar contenido automáticamente.

## Desarrollo

Este proyecto utiliza CrewAI para orquestar tres agentes de IA:
- Investigador: Busca información relevante sobre el tema
- Escritor: Crea el artículo basado en la investigación
- Editor: Revisa y mejora el artículo final

Para más detalles sobre la implementación, consulta los archivos en el directorio `Backend/app/crew/`.

## Contribuir

Las contribuciones son bienvenidas. Por favor, sigue estos pasos:

1. Haz un fork del repositorio
2. Crea una nueva rama (`git checkout -b feature/AmazingFeature`)
3. Haz commit de tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Distribuido bajo la Licencia MIT. Ver `LICENSE` para más información.

## Contacto

Cristian Aracena - info@coda.uno

Enlace del proyecto: https://github.com/matiasA/CodaPost