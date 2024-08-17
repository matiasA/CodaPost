from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from ..crew.crew import NewsCrew
   
router = APIRouter()
   
class ContentRequest(BaseModel):
    topic: str
    structure: str = 'parrafos'
    writing_style: str = 'formal'
    post_length: str = 'medio'
   
@router.post("/generate_content")
async def generate_content(request: ContentRequest):
    try:
        crew = NewsCrew(request.topic, request.structure, request.writing_style, request.post_length)
        result = crew.run()
        return {"content": result, "status": "success"}
    except Exception as e:
        return {"error": str(e), "status": "error"}