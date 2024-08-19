from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from ..crew.crew import NewsCrew
import logging

router = APIRouter()
logger = logging.getLogger(__name__)

class WordPressPostRequest(BaseModel):
    topic: str
    structure: str = 'parrafos'
    writing_style: str = 'formal'
    post_length: str = 'medio'
    post_type: str = 'artículo de investigación'
    wp_username: str
    wp_password: str
    wp_url: str

@router.post("/generate_wordpress_post")
async def generate_wordpress_post(request: WordPressPostRequest):
    try:
        # Generar el contenido
        crew = NewsCrew(request.topic, request.structure, request.writing_style, request.post_length, request.post_type)
        content = crew.run()

        # Aquí iría la lógica para publicar en WordPress
        # Por ahora, solo simularemos la publicación
        logger.info(f"Simulando publicación en WordPress: {request.wp_url}")
        
        return {"status": "success", "message": "Contenido generado y publicado en WordPress", "content": content}
    except Exception as e:
        logger.error(f"Error en generate_wordpress_post: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Error interno del servidor: {str(e)}")