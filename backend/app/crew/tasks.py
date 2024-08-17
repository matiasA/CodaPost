from crewai import Task
   
class NewsTasks:
    def research_task(self, agent, topic):
        return Task(
            description=f"Investiga el tema: {topic}. Recopila datos relevantes, estadísticas y citas de fuentes confiables.",
            agent=agent,
            expected_output="Un resumen detallado de la investigación con datos clave, estadísticas y citas relevantes."
        )
   
    def writing_task(self, agent, research_result, structure, writing_style, post_length):
        return Task(
            description=f"Escribe un artículo de noticias basado en la siguiente investigación: {research_result}. "
                        f"Utiliza la estructura: {structure}, el estilo de escritura: {writing_style}, "
                        f"y una longitud de post: {post_length}.",
            agent=agent,
            expected_output="Un artículo de noticias completo y bien estructurado basado en la investigación proporcionada."
        )
   
    def editing_task(self, agent, article):
        return Task(
            description=f"Revisa y mejora el siguiente artículo: {article}. Asegúrate de que sea claro, conciso y objetivo.",
            agent=agent,
            expected_output="Un artículo de noticias revisado y mejorado, listo para su publicación."
        )