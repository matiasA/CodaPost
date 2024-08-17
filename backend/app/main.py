from fastapi import FastAPI
from app.routers import generate_content
import logging

logging.basicConfig(level=logging.INFO)

app = FastAPI()

app.include_router(generate_content.router)

@app.get("/")
async def root():
    return {"message": "Bienvenido a la API de generación de noticias"}