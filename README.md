# Generador de Noticias con CrewAI CodaPost

Este proyecto es una API de generación de noticias que utiliza CrewAI y OpenAI para crear artículos de noticias personalizados sobre diversos temas.

## Características

- Generación automática de artículos de noticias utilizando GPT-4o de OpenAI
- API RESTful construida con FastAPI
- Uso de CrewAI para orquestar agentes de IA (investigador, escritor, editor)
- Búsqueda en tiempo real de información utilizando SerperDevTool
- Opciones personalizables para la estructura, estilo de escritura y longitud del artículo
- Logging detallado del proceso de generación

## Requisitos

- Python 3.12 o superior
- Clave API de OpenAI
- Clave API de Serper

## Instalación

1. Clona este repositorio:
   ```
   git clone [URL del repositorio]
   ```
2. Navega al directorio del proyecto:
   ```
   cd [nombre del directorio]
   ```
3. Crea un entorno virtual:
   ```
   python -m venv backend/venv
   ```
4. Activa el entorno virtual:
   - En Windows: `backend\venv\Scripts\activate`
   - En macOS y Linux: `source backend/venv/bin/activate`
5. Instala las dependencias:
   ```
   pip install -r backend/requirements.txt
   ```

## Configuración

1. Crea un archivo `.env` en el directorio `backend/app` con las siguientes variables:
   ```
   OPENAI_API_KEY=tu_clave_api_de_openai
   SERPER_API_KEY=tu_clave_api_de_serper
   ```

## Uso

1. Inicia el servidor FastAPI:
   ```
   uvicorn backend.app.main:app --reload
   ```
2. Accede a la documentación de la API en `http://localhost:8000/docs`
3. Utiliza el endpoint `/generate_content` para generar artículos de noticias

## Desarrollo

Este proyecto utiliza CrewAI para orquestar tres agentes de IA:
- Investigador: Busca información relevante sobre el tema
- Escritor: Crea el artículo basado en la investigación
- Editor: Revisa y mejora el artículo final

Para más detalles sobre la implementación, consulta los archivos en el directorio `backend/app/crew/`.

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