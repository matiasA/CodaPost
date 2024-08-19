from crewai import Agent
from langchain_openai import ChatOpenAI
from langchain_groq import ChatGroq
from crewai_tools import SerperDevTool

class NewsAgents:
    def __init__(self):
        self.gpt4 = ChatOpenAI(model="gpt-4o-mini", temperature=0.7, max_tokens=1500)
        self.llama = ChatGroq(model="llama-3.1-70b-versatile", temperature=0.7, max_tokens=1500)
        self.search_tool = SerperDevTool()

    def researcher(self):
        return Agent(
            role='Investigador de Noticias',
            goal='Recopilar información relevante y actualizada sobre temas de noticias',
            backstory='Eres un investigador experto en encontrar información precisa y relevante sobre diversos temas de actualidad.',
            allow_delegation=False,
            llm=self.gpt4,
            tools=[self.search_tool],
            verbose=True,
            max_iterations=1
        )

    def writer(self, post_type):
        return Agent(
            role='Escritor de Noticias',
            goal=f'Escribir un {post_type} conciso y atractivo',
            backstory='Eres un periodista experimentado con habilidad para escribir artículos claros y objetivos.',
            allow_delegation=False,
            llm=self.llama,
            verbose=True,
            max_iterations=1
        )

    def editor(self):
        return Agent(
            role='Editor de Noticias',
            goal='Revisar y mejorar los artículos de noticias',
            backstory='Eres un editor meticuloso con años de experiencia en la industria de las noticias.',
            allow_delegation=False,
            llm=self.gpt4,
            verbose=True,
            max_iterations=1
        )