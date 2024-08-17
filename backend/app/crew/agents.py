from crewai import Agent
from langchain_openai import ChatOpenAI
from crewai_tools import SerperDevTool

class NewsAgents:
    def __init__(self):
        self.llm = ChatOpenAI(model="gpt-4o-mini")
        self.search_tool = SerperDevTool()

    def researcher(self):
        return Agent(
            role='Investigador de Noticias',
            goal='Recopilar información relevante y actualizada sobre temas de noticias',
            backstory='Eres un investigador experto en encontrar información precisa y relevante sobre diversos temas de actualidad.',
            allow_delegation=False,
            llm=self.llm,
            tools=[self.search_tool]
        )

    def writer(self):
        return Agent(
            role='Escritor de Noticias',
            goal='Escribir artículos de noticias concisos y atractivos',
            backstory='Eres un periodista experimentado con habilidad para escribir artículos claros y objetivos.',
            allow_delegation=False,
            llm=self.llm
        )

    def editor(self):
        return Agent(
            role='Editor de Noticias',
            goal='Revisar y mejorar los artículos de noticias',
            backstory='Eres un editor meticuloso con años de experiencia en la industria de las noticias.',
            allow_delegation=False,
            llm=self.llm
        )