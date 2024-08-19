from crewai import Task
   
class NewsTasks:
    def research_task(self, agent, topic, post_type):
        return Task(
            description=f"Investiga el tema: {topic} para un {post_type}. Recopila datos relevantes, estadísticas y citas de fuentes confiables.",
            agent=agent,
            expected_output="Un resumen detallado de la investigación con datos clave, estadísticas y citas relevantes."
        )
   
    def writing_task(self, agent, research_result, structure, writing_style, post_length, post_type):
        return Task(
            description=f"Escribe un {post_type} basado en la siguiente investigación: {research_result}. "
                        f"Utiliza la estructura: {structure}, el estilo de escritura: {writing_style}, "
                        f"y una longitud de post: {post_length}.",
            agent=agent,
            expected_output=f"Un {post_type} completo y bien estructurado basado en la investigación proporcionada."
        )
   
    def editing_task(self, agent, article, post_type):
        return Task(
            description=f"Revisa y mejora el siguiente {post_type}: {article}. Asegúrate de que sea claro, conciso y objetivo.",
            agent=agent,
            expected_output=f"Un {post_type} revisado y mejorado, listo para su publicación."
        )