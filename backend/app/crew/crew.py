from crewai import Crew
from .agents import NewsAgents
from .tasks import NewsTasks
from ..config import OPENAI_API_KEY, SERPER_API_KEY
import os
import logging

os.environ["OPENAI_API_KEY"] = OPENAI_API_KEY
os.environ["SERPER_API_KEY"] = SERPER_API_KEY

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class NewsCrew:
    def __init__(self, topic, structure='parrafos', writing_style='formal', post_length='medio', post_type='artículo de investigación'):
        self.topic = topic
        self.structure = structure
        self.writing_style = writing_style
        self.post_length = post_length
        self.post_type = post_type
        self.agents = NewsAgents()
        self.tasks = NewsTasks()
   
    def run(self):
        def task_callback(task):
            logger.info(f"Tarea completada: {task.description}")
            logger.info(f"Resultado: {task.output}")

        try:
            crew = Crew(
                agents=[
                    self.agents.researcher(),
                    self.agents.writer(self.post_type),
                    self.agents.editor()
                ],
                tasks=[
                    self.tasks.research_task(self.agents.researcher(), self.topic, self.post_type),
                    self.tasks.writing_task(self.agents.writer(self.post_type), "{{research_result}}", self.structure, self.writing_style, self.post_length, self.post_type),
                    self.tasks.editing_task(self.agents.editor(), "{{article}}", self.post_type)
                ],
                verbose=True,
                callback=task_callback
            )
            
            result = crew.kickoff()
            logger.info("Proceso de generación de contenido completado")
            return result
        except Exception as e:
            logger.error(f"Error durante la ejecución de la crew: {str(e)}")
            raise