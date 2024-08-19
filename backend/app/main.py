from fastapi import FastAPI
from app.routers import generate_content, wordpress
import logging

logging.basicConfig(level=logging.INFO)

app = FastAPI()

app.include_router(generate_content.router)
app.include_router(wordpress.router)

@app.get("/")
async def root():
    return {"message": "Bienvenido a la API de generación de noticias"}