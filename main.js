// Interactive Portal Script
document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initVideoPlayer();
  initModals();
  initCalculators();
  initNewsFilter();
  handleHashNavigation();
});

// Articles Database (Direct 1-to-1 Spanish Translations from kor2.njaccessportal.com)
export const articlesData = {
  "eye-drops": {
    title: "Más de 2 millones de frascos de gotas oftálmicas retirados del mercado por posible contaminación",
    category: "Retiro de Mercado FDA",
    date: "4 de agosto de 2026",
    readTime: "2 min de lectura",
    image: "https://images.unsplash.com/photo-1628771065117-74ccb5690668?w=1200&q=80&auto=format",
    content: `
      <p>Lupin Pharmaceuticals ha anunciado el retiro voluntario del mercado de aproximadamente 2.5 millones de frascos de suspensión oftálmica de prednisolona recetada al 1% debido a la posible presencia de partículas extrañas. La Administración de Alimentos y Medicamentos de EE. UU. (FDA) ha clasificado este retiro como Clase II, lo que significa que el uso del producto afectado puede provocar problemas de salud temporales o reversibles.</p>
      
      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Cómo verificar los productos afectados por el retiro</h4>
      <p>Si actualmente está utilizando este producto, verifique los siguientes datos:</p>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li><strong>Nombre del producto:</strong> Prednisolone Acetate Ophthalmic Suspension USP, 1%</li>
        <li><strong>Fabricante:</strong> Lupin Pharmaceuticals</li>
        <li><strong>Número de lote:</strong> Verifique en el aviso oficial de retiro de la FDA (fda.gov/safety/recalls)</li>
        <li><strong>Período de distribución:</strong> Lotes fabricados entre enero de 2025 y junio de 2026</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Medidas inmediatas que debe tomar</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li>Suspenda inmediatamente el uso del producto afectado.</li>
        <li>Comuníquese con su médico de cabecera o oftalmólogo para consultar sobre su receta actual.</li>
        <li>Devuelva los productos sin abrir al lugar de compra para recibir un reembolso.</li>
        <li>Si experimenta alguna reacción adversa, infórmelo a FDA MedWatch (1-800-FDA-1088).</li>
        <li>⚠️ <strong>ADVERTENCIA:</strong> Suspender abruptamente las gotas oftálmicas de esteroides puede empeorar los síntomas. Asegúrese de consultar a su médico antes de solicitar una receta de reemplazo.</li>
      </ul>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> FDA.gov — Lupin Pharmaceuticals Voluntarily Recalls Eye Drops
      </p>
    `
  },
  "ai-health-assistant": {
    title: "El Asistente de Salud AI apoya el control de medicamentos complejos en adultos mayores",
    category: "Salud & Tecnología",
    date: "2 de agosto de 2026",
    readTime: "3 min de lectura",
    image: "https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1200&q=80&auto=format",
    content: `
      <p>Se han publicado los resultados de una investigación que demuestra cómo las aplicaciones de gestión de la salud basadas en Inteligencia Artificial (IA) y los asistentes digitales están brindando una ayuda práctica a los pacientes de edad avanzada que deben administrar horarios complejos de medicamentos. Esta tecnología resulta especialmente beneficiosa para pacientes que padecen múltiples enfermedades crónicas simultáneas y deben tomar diversos fármacos.</p>
      
      <p>Más del 40% de los adultos mayores de 65 años toman 5 o más medicamentos recetados, y se estima que los errores en la administración de medicamentos están relacionados con más de 7,000 muertes al año en los Estados Unidos.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Funciones principales del Asistente de Salud AI</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li><strong>Recordatorios de medicamentos:</strong> Notificaciones precisas según un calendario personalizado.</li>
        <li><strong>Verificación de interacción de medicamentos:</strong> Advertencias sobre interacciones peligrosas entre los fármacos ingeridos.</li>
        <li><strong>Seguimiento de recargas:</strong> Recordatorios automáticos para renovar recetas médicas en la farmacia.</li>
        <li><strong>Gestión de registros de salud:</strong> Registro automático y seguimiento de indicadores de salud como presión arterial y glucemia.</li>
        <li><strong>Comunicación con el equipo médico:</strong> Facilidad para compartir información de salud con su médico tratante.</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Recomendaciones para la comunidad hispana</h4>
      <p>Existe un número creciente de aplicaciones que ofrecen interfaz totalmente en español para superar las barreras del idioma. Si le resulta difícil usar un teléfono inteligente, consulte con nuestro centro o centro comunitario local para recibir programas de orientación.</p>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> Journal of Medical Internet Research — AI-Powered Medication Management for Older Adults
      </p>
    `
  },
  "living-longer": {
    title: "El enfoque de la longevidad cambia de 'Vivir más' a 'Vivir con mejor calidad de vida'",
    category: "Geriatría & Bienestar",
    date: "2 de agosto de 2026",
    readTime: "3 min de lectura",
    image: "https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200&q=80&auto=format",
    content: `
      <p>Aunque los dispositivos antienvejecimiento y los costosos suplementos para la longevidad ocupan con frecuencia los titulares de los medios, los principales expertos en geriatría advierten que extender la esperanza de vida tiene poco sentido si los años adicionales transcurren únicamente en el manejo de enfermedades graves. Según las estadísticas, mientras que el 60% de los jóvenes padece al menos una enfermedad crónica, entre los adultos mayores este porcentaje aumenta drásticamente al 90%. Las enfermedades cardíacas, la diabetes tipo 2, los accidentes cerebrovasculares y la degeneración articular siguen siendo las causas principales de discapacidad física en la madurez.</p>

      <p>Destacados biogerontólogos explican que, aunque el envejecimiento en sí no es una enfermedad, es un proceso que vuelve al organismo mucho más vulnerable a las afecciones crónicas. Aunque tecnologías futuristas como la edición genética CRISPR muestran potencial en laboratorio, los médicos enfatizan que adoptar hábitos de vida sencillos y comprobados sigue siendo la estrategia más eficaz para mantener una vida independiente y activa.</p>

      <p>Adoptar una dieta mediterránea, combinar ejercicio aeróbico con entrenamiento de fuerza, dormir entre 7 y 8 horas por noche y mantener interacción social regular son métodos científicamente probados para aumentar el 'Healthspan' (esperanza de vida con buena salud). Prevenir enfermedades crónicas entre los 40 y 50 años es mucho más sencillo y económico que tratarlas a los 70 años.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Resumen Clave</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li>Es más importante extender el 'Healthspan' (período de vida saludable e independiente) que simplemente aumentar la longevidad total.</li>
        <li>9 de cada 10 adultos mayores tienen al menos una enfermedad crónica como hipertensión o diabetes.</li>
        <li>Elecciones de estilo de vida sencillas como la nutrición mediterránea, el ejercicio de fuerza y el descanso profundo siguen siendo la mejor respuesta comprobada.</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Pasos Recomendados</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li>Programe su examen médico anual completo para revisar sus indicadores básicos de salud (presión arterial, glucosa, colesterol).</li>
        <li>Seleccione un objetivo de salud diario y alcanzable, como caminar 15 minutos después de cenar o añadir una porción de vegetales de hoja verde a su almuerzo.</li>
      </ul>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> CNN Health — Aging better isn't just about adding more years. Tech to reduce chronic disease is just as important by Madeline Holcombe
      </p>
    `
  },
  "fda-corazon": {
    title: "Retiro de la FDA: Cerca de 1 millón de frascos de medicamentos cardíacos y renales retirados",
    category: "Retiro de Mercado FDA",
    date: "30 de julio de 2026",
    readTime: "2 min de lectura",
    image: "https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?w=1200&q=80&auto=format",
    content: `
      <p>La compañía farmacéutica global Amgen ha anunciado el retiro voluntario de lotes específicos de dos medicamentos, Corlanor (ivabradina) y Sensipar (cinacalcet), debido a la detección de partículas extrañas imprevistas durante el proceso de recubrimiento de las tabletas.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Medicamentos afectados por el retiro</h4>
      <p><strong>Corlanor (Ivabradine)</strong></p>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.3rem;">
        <li><strong>Uso:</strong> Tratamiento de la insuficiencia cardíaca crónica.</li>
        <li><strong>Motivo del retiro:</strong> Presencia de partículas extrañas en el polvo de recubrimiento.</li>
        <li><strong>Lotes afectados:</strong> Consulte el aviso oficial de la FDA.</li>
      </ul>

      <p><strong>Sensipar (Cinacalcet)</strong></p>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.3rem;">
        <li><strong>Uso:</strong> Tratamiento del hiperparatiroidismo secundario en pacientes con enfermedad renal crónica.</li>
        <li><strong>Motivo del retiro:</strong> Mismo problema de recubrimiento por partículas.</li>
        <li><strong>Lotes afectados:</strong> Consulte el aviso oficial de la FDA.</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Guía para pacientes en tratamiento</h4>
      <p>Si actualmente toma alguno de estos medicamentos:</p>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li>Comuníquese de inmediato con su médico o farmacéutico para verificar si su lote está incluido en el retiro.</li>
        <li>No suspenda el medicamento sin consultar a su médico tratante — la interrupción abrupta puede ser extremadamente peligrosa para pacientes con insuficiencia cardíaca o enfermedad renal.</li>
        <li>Siga las instrucciones del personal médico para obtener una prescripción alternativa.</li>
        <li><strong>IMPORTANTE:</strong> Este retiro es una medida de precaución preventiva; hasta la fecha no se han reportado daños a la salud relacionados con las partículas extrañas.</li>
      </ul>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> FDA.gov — Amgen Voluntary Recall of Corlanor and Sensipar Tablets
      </p>
    `
  },
  "glp1-medicare": {
    title: "Medicare inicia proyecto piloto para la cobertura de tratamientos GLP-1 contra la obesidad y salud cardíaca",
    category: "Medicare & ACA",
    date: "30 de julio de 2026",
    readTime: "3 min de lectura",
    image: "https://images.unsplash.com/photo-1607874963930-2edecc67a25a?w=1200&q=80&auto=format",
    content: `
      <p>Medicare ha anunciado el lanzamiento oficial de un proyecto piloto de cobertura para medicamentos agonistas de GLP-1 destinados a la reducción de peso y la mejora de la salud cardíaca. Se espera que esta política mejore drásticamente el acceso de millones de beneficiarios de Medicare a medicamentos de alto costo como Ozempic y Wegovy.</p>

      <p>Anteriormente, la Parte D de Medicare prohibía expresamente la cobertura de fármacos prescritos únicamente para el control de peso. Sin embargo, este programa piloto adopta un nuevo enfoque al expandir la cobertura de estos medicamentos basándose en la evidencia clínica que demuestra la reducción del riesgo cardiovascular.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Aspectos clave del programa</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li><strong>Elegibilidad:</strong> Beneficiarios de Medicare con antecedentes de enfermedad cardiovascular o en grupo de alto riesgo.</li>
        <li><strong>Medicamentos incluidos:</strong> Fármacos aprobados por la FDA de la clase GLP-1 (incluyendo semaglutida).</li>
        <li><strong>Costo para el paciente:</strong> Se anticipa una reducción significativa en los copagos de bolsillo mediante la cobertura de la Parte D.</li>
        <li><strong>Fecha de inicio:</strong> Se prevé el funcionamiento piloto para la segunda mitad de 2026.</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Orientación para residentes de Nueva Jersey</h4>
      <p>Si reside en Nueva Jersey, comuníquese con su centro local de orientación sobre Medicare para verificar su elegibilidad y el proceso de solicitud. El Centro de Acceso a la Salud de NJ ofrece servicios de consulta gratuitos en español.</p>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> CMS.gov — Medicare Coverage of Obesity Medications Initiative
      </p>
    `
  },
  "sueno-demencia": {
    title: "Sueño profundo y la eliminación de residuos cerebrales: Clave en la prevención de la demencia",
    category: "Neurología",
    date: "29 de julio de 2026",
    readTime: "3 min de lectura",
    image: "https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=1200&q=80&auto=format",
    content: `
      <p>Una nueva investigación revela que el sueño profundo y de alta calidad desempeña un papel crucial en la activación del sistema glinfático, el mecanismo natural de depuración del cerebro que elimina las proteínas tóxicas asociadas con la enfermedad de Alzheimer y otras formas de demencia.</p>

      <p>Los científicos han descubierto que durante el sueño, especialmente en la fase de onda lenta (sueño profundo), el líquido cefalorraquídeo fluye activamente a través del tejido cerebral para limpiar residuos acumulados como las proteínas beta-amiloide y tau, identificadas como causas principales del Alzheimer.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Prácticas para un sueño saludable</h4>
      <p>Los especialistas en medicina del sueño recomiendan las siguientes pautas de higiene del descanso:</p>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li><strong>Horario regular:</strong> Mantener el mismo horario para acostarse y levantarse, incluso los fines de semana.</li>
        <li><strong>Ambiente ideal:</strong> Dormir en un espacio oscuro, silencioso y fresco (entre 18°C y 20°C).</li>
        <li><strong>Bloqueo de luz azul:</strong> Evitar el uso de teléfonos inteligentes y tabletas 1 a 2 horas antes de dormir.</li>
        <li><strong>Limitación de cafeína:</strong> Evitar el consumo de cafeína después de las 2:00 p.m.</li>
        <li><strong>Ejercicio regular:</strong> Practicar actividad física diaria, evitando ejercicios intensos 3 horas antes de dormir.</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">El papel del descanso en la prevención del deterioro cognitivo</h4>
      <p>Los estudios indican que la falta de sueño crónica (menos de 6 horas por noche) puede aumentar el riesgo de desarrollar demencia hasta en un 30%. Por el contrario, las personas que disfrutan de 7 a 8 horas de sueño reparador cada noche muestran una tasa significativamente menor de deterioro cognitivo.</p>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> Nature Neuroscience — Glymphatic System Function During Sleep
      </p>
    `
  },
  "ejercicio-fuerza": {
    title: "Entrenamiento de fuerza después de los 60: Fundamental para prevenir caídas y mantener la autonomía",
    category: "Geriatría & Salud Física",
    date: "29 de julio de 2026",
    readTime: "3 min de lectura",
    image: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=1200&q=80&auto=format",
    content: `
      <p>Investigaciones recientes reafirman que el entrenamiento de fuerza después de los 60 años desempeña un papel vital en la prevención de caídas y en el mantenimiento de una vida independiente. El Colegio Estadounidense de Medicina del Deporte (ACSM) recomienda que los adultos mayores de 65 años realicen al menos dos sesiones semanales de ejercicios de fortalecimiento muscular.</p>

      <p>La sarcopenia (pérdida progresiva de masa muscular) comienza a partir de los 40 años, reduciendo la masa muscular entre un 3% y un 8% cada década. Sin embargo, está científicamente demostrado que el ejercicio de resistencia adecuado puede ralentizar o revertir este proceso.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">5 Ejercicios de fuerza seguros para adultos mayores</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li><strong>Sentadillas en silla:</strong> Levantarse y sentarse en una silla de 10 a 15 veces.</li>
        <li><strong>Flexiones en pared:</strong> Flexiones de brazos adaptadas apoyando las manos firmemente contra una pared.</li>
        <li><strong>Elevación de talones:</strong> Elevar ambos talones mientras sostiene el borde de la mesa o barra de apoyo.</li>
        <li><strong>Remo con banda elástica:</strong> Fortalecimiento de la espalda mediante bandas de resistencia.</li>
        <li><strong>Ejercicio de equilibrio:</strong> Mantenerse sobre un solo pie (cerca de una silla de apoyo por seguridad).</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">La importancia crítica de prevenir caídas</h4>
      <p>1 de cada 4 adultos mayores de 65 años sufre una caída cada año en EE. UU. Las caídas son la causa principal de hospitalización por traumatismos en adultos mayores, y las fracturas de cadera conllevan una tasa de mortalidad de hasta el 30% en el primer año. Desarrollar fuerza y equilibrio reduce el riesgo de caídas hasta en un 50%.</p>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> ACSM — Physical Activity Guidelines for Older Adults 2026
      </p>
    `
  },
  "tecnologia-homecare": {
    title: "Medicare expande la cobertura de tecnologías avanzadas de atención médica domiciliaria para pacientes crónicos",
    category: "Medicare & ACA",
    date: "26 de julio de 2026",
    readTime: "3 min de lectura",
    image: "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1200&q=80&auto=format",
    content: `
      <p>Medicare ha implementado una nueva política que amplía de manera sustancial la cobertura de tecnologías médicas domiciliarias para beneficiarios con enfermedades crónicas. Esta actualización permite a los pacientes con diabetes, insuficiencia cardíaca o enfermedad pulmonar obstructiva crónica (EPOC) recibir un seguimiento de salud más preciso desde la comodidad de su hogar.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Lista de tecnologías aprobadas para cobertura</h4>
      <ul style="padding-left:1.25rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.4rem;">
        <li><strong>Monitores Continuos de Glucosa (MCG):</strong> Dispositivos de seguimiento de glucemia en tiempo real para pacientes con diabetes.</li>
        <li><strong>Monitoreo Remoto de Pacientes (RPM):</strong> Transmisión remota de presión arterial, saturación de oxígeno y frecuencia cardíaca.</li>
        <li><strong>Estetoscopios Digitales:</strong> Permiten a los médicos auscultar ruidos cardíacos y pulmonares a distancia.</li>
        <li><strong>Dispensadores Inteligentes de Medicamentos:</strong> Dosificación y verificación automática de ingesta de fármacos.</li>
      </ul>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Cómo solicitar el servicio</h4>
      <p>Estas tecnologías están cubiertas bajo la Parte B de Medicare si su médico emite una prescripción médica por necesidad diagnóstica. Los residentes de Nueva Jersey pueden comunicarse con nuestro centro para recibir asistencia y orientación personalizada en español.</p>

      <h4 style="margin:1.5rem 0 0.5rem; color:#111111; font-family:var(--font-serif); font-size:1.15rem; font-weight:700;">Expansión de la Telemedicina</h4>
      <p>Las flexibilidades de atención por telemedicina implementadas inicialmente durante la pandemia se han extendido formalmente durante 2026. Ahora puede realizar consultas médicas virtuales por video y recibir sus recetas sin salir de su hogar.</p>

      <p style="font-size:0.8rem; color:#6b7280; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:0.75rem;">
        <strong>Fuente original:</strong> CMS.gov — Medicare Coverage of Remote Patient Monitoring and Home Health Technology
      </p>
    `
  }
};

// Initial Comments Preset per article
const defaultComments = {
  "eye-drops": [
    { nickname: "María_Hackensack", text: "Muchas gracias por publicar esto en español. Mi mamá usa estas gotas de prednisolona, ya mismo llamo a la farmacia.", date: "Hace 2 horas" },
    { nickname: "Carlos_NJ", text: "Excelente información. ¿Saben si en Walgreens reemplazan el medicamento directamente?", date: "Hace 5 horas" }
  ],
  "ai-health-assistant": [
    { nickname: "Sra_Elena", text: "A mi esposo se le olvida tomar sus pastillas de la presión a tiempo, probaré este tipo de asistente.", date: "Ayer" },
    { nickname: "Jorge_Union", text: "Muy útil para las personas mayores que no leen inglés fluido en los frascos de farmacia.", date: "Hace 2 días" }
  ]
};

// Get Comments from LocalStorage or Defaults
function getComments(articleId) {
  const key = 'comments_' + articleId;
  const stored = localStorage.getItem(key);
  if (stored) {
    try {
      return JSON.parse(stored);
    } catch (e) {
      console.error(e);
    }
  }
  return defaultComments[articleId] || [
    { nickname: "Vecino_NJ", text: "Gracias por mantener actualizada a nuestra comunidad con noticias de salud claras en español.", date: "Hace 1 día" }
  ];
}

// Render Comments List
window.renderComments = function(articleId) {
  const listEl = document.getElementById(`commentsList-${articleId}`);
  const countEl = document.getElementById(`commentCount-${articleId}`);
  if (!listEl) return;

  const comments = getComments(articleId);
  if (countEl) countEl.textContent = comments.length;

  if (comments.length === 0) {
    listEl.innerHTML = `<p style="font-size:0.85rem; color:#64748b; font-style:italic;">No hay comentarios aún. ¡Sé el primero en comentar!</p>`;
    return;
  }

  listEl.innerHTML = comments.map(c => `
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:4px; padding:0.85rem 1rem; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.35rem;">
        <span style="font-weight:700; font-size:0.875rem; color:#c91818; display:flex; align-items:center; gap:0.4rem;">
          <span style="width:24px; height:24px; border-radius:50%; background:#fef2f2; color:#c91818; display:inline-flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:800;">
            ${c.nickname.charAt(0).toUpperCase()}
          </span>
          ${c.nickname}
        </span>
        <span style="font-size:0.75rem; color:#94a3b8;">${c.date}</span>
      </div>
      <p style="font-size:0.85rem; color:#334155; line-height:1.45; margin-top:0.25rem;">
        ${c.text}
      </p>
    </div>
  `).join('');
};

// Add Comment Function
window.addComment = function(articleId) {
  const nickInput = document.getElementById(`commentNickname-${articleId}`);
  const textInput = document.getElementById(`commentText-${articleId}`);

  if (!nickInput || !textInput) return;

  const nickname = nickInput.value.trim();
  const text = textInput.value.trim();

  if (!nickname || !text) return;

  const comments = getComments(articleId);
  comments.unshift({
    nickname: nickname,
    text: text,
    date: "Hace un momento"
  });

  localStorage.setItem('comments_' + articleId, JSON.stringify(comments));
  textInput.value = '';
  renderComments(articleId);
};

// Global function to open full article in modal with Comment Section
window.openArticleModal = async function(id) {
  let article = articlesData[id];
  if (!article) {
    try {
      const res = await fetch(`/api/posts.php?slug=${encodeURIComponent(id)}`);
      const data = await res.json();
      if (data.success && data.data) {
        article = {
          title: data.data.title,
          category: data.data.category,
          date: data.data.date,
          readTime: data.data.readTime || '3 min de lectura',
          image: data.data.coverImage || (data.data.images && data.data.images[0]),
          content: data.data.content
        };
        articlesData[id] = article;
      }
    } catch(e) {}
  }
  if (!article) return;

  const html = `
    <div>
      <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.75rem; font-weight:800; color:#c91818; text-transform:uppercase; margin-bottom:0.5rem;">
        <span>${article.category}</span>
        <span style="color:#cbd5e1;">·</span>
        <span style="color:#64748b; font-weight:500;">${article.date}</span>
        <span style="color:#cbd5e1;">·</span>
        <span style="color:#64748b; font-weight:500;">⏱ ${article.readTime}</span>
      </div>

      <h2 style="font-family:var(--font-serif); font-size:1.65rem; font-weight:900; color:#111111; line-height:1.25; margin-bottom:1.25rem;">
        ${article.title}
      </h2>

      ${article.image ? `
        <div style="width:100%; height:300px; border-radius:4px; overflow:hidden; margin-bottom:1.5rem; background:#f1f5f9;">
          <img src="${article.image}" alt="${article.title}" style="width:100%; height:100%; object-fit:cover;">
        </div>
      ` : ''}

      <div style="font-size:0.95rem; color:#1f2937; line-height:1.75; display:flex; flex-direction:column; gap:1.15rem; font-family:var(--font-serif);">
        ${article.content}
      </div>

      <div style="margin-top:2rem; padding-top:1rem; border-top:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <span style="font-size:0.8rem; color:#64748b;">¿Necesita asistencia médica sobre este artículo?</span>
        <a href="tel:+18009997200" class="btn-news-red" style="font-size:0.8rem; padding:0.4rem 1rem;">📞 1-800-999-7200</a>
      </div>

      <!-- Comment Section -->
      <div class="comments-section" style="margin-top:2rem; padding-top:1.5rem; border-top:2px solid #111111;">
        <h3 style="font-family:var(--font-sans); font-size:1.1rem; font-weight:900; color:#111111; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
          <span>💬 Comentarios de la Comunidad (<span id="commentCount-${id}">0</span>)</span>
          <span style="font-size:0.75rem; color:#c91818; background:#fef2f2; padding:0.2rem 0.6rem; border-radius:2px; font-weight:800;">Sin necesidad de registro</span>
        </h3>

        <!-- Comment Input Form -->
        <form onsubmit="event.preventDefault(); (window.submitCommentApi ? window.submitCommentApi('${id}') : addComment('${id}'))" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:4px; padding:1.25rem; margin-bottom:1.5rem;">
          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.75rem; font-weight:800; color:#111111; text-transform:uppercase; margin-bottom:0.3rem;">Apodo / Nickname:</label>
            <input type="text" id="commentNickname-${id}" placeholder="Ej. Maria_NJ o Vecino_Bergen" required style="width:100%; padding:0.6rem 0.85rem; border-radius:2px; border:1px solid #cbd5e1; font-size:0.875rem; outline:none; font-family:var(--font-sans);">
          </div>
          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.75rem; font-weight:800; color:#111111; text-transform:uppercase; margin-bottom:0.3rem;">Su Comentario:</label>
            <textarea id="commentText-${id}" rows="3" placeholder="Escriba su opinión o pregunta sobre este artículo..." required style="width:100%; padding:0.6rem 0.85rem; border-radius:2px; border:1px solid #cbd5e1; font-size:0.875rem; outline:none; font-family:var(--font-sans);"></textarea>
          </div>
          <button type="submit" class="btn-news-red" style="font-size:0.8rem; padding:0.5rem 1.25rem;">Publicar Comentario</button>
        </form>

        <!-- Comments Output -->
        <div id="commentsList-${id}" style="display:flex; flex-direction:column; gap:0.85rem;"></div>
      </div>
    </div>
  `;

  openModal(article.category, html);
  if (window.fetchComments) {
    window.fetchComments(id);
  } else {
    setTimeout(() => renderComments(id), 50);
  }
};

// Handle Hash Navigation
function handleHashNavigation() {
  const hash = window.location.hash.replace('#', '');
  if (hash) {
    setTimeout(() => {
      const element = document.getElementById(hash);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      openArticleModal(hash);
    }, 200);
  }
}

// Mobile Menu Toggle
function initMobileMenu() {
  const toggleBtn = document.getElementById('mobileMenuToggle');
  const desktopNav = document.querySelector('.nav-links-row');

  if (toggleBtn && desktopNav) {
    toggleBtn.addEventListener('click', () => {
      const isFlex = desktopNav.style.display === 'flex';
      desktopNav.style.display = isFlex ? '' : 'flex';
      desktopNav.style.flexDirection = 'column';
      desktopNav.style.position = 'absolute';
      desktopNav.style.top = '100%';
      desktopNav.style.left = '0';
      desktopNav.style.right = '0';
      desktopNav.style.background = '#ffffff';
      desktopNav.style.padding = '1rem';
      desktopNav.style.borderBottom = '2px solid #111111';
    });
  }
}

// Video Player & Playlist Manager
let videoData = [
  {
    id: "v_1",
    title: "Conozca los principales cambios y aumentos en Medicare para 2026",
    category: "Medicare",
    speaker: "Noticias Telemundo & Asesores de Salud",
    views: "145,000 vistas",
    duration: "10:00",
    youtubeId: "TsdumJbTpTY",
    youtubeUrl: "https://www.youtube.com/watch?v=TsdumJbTpTY",
    thumbnail: "https://i.ytimg.com/vi/TsdumJbTpTY/hqdefault.jpg",
    desc: "Reportaje informativo sobre los ajustes en los costos de salud, hospitalizaciones, deducibles y cambios de cobertura para los beneficiarios de Medicare en 2026."
  },
  {
    id: "v_2",
    title: "¿Cómo detectar si alguien está sufriendo un derrame cerebral (ACV)?",
    category: "Neurología",
    speaker: "Noticias Telemundo & Especialistas en Neurología",
    views: "112,000 vistas",
    duration: "08:40",
    youtubeId: "7J0FeALmzws",
    youtubeUrl: "https://www.youtube.com/watch?v=7J0FeALmzws",
    thumbnail: "https://i.ytimg.com/vi/7J0FeALmzws/hqdefault.jpg",
    desc: "Aprenda a detectar las señales tempranas de un accidente cerebrovascular (ACV) y cómo actuar durante la ventana crítica de atención médica de urgencia."
  },
  {
    id: "v_3",
    title: "La Enfermedad Cardíaca en la Mujer: Consejos Médicos de Prevención",
    category: "Cardiovascular",
    speaker: "Mayo Clinic en Español & Dra. Carmen Terzic",
    views: "128,000 vistas",
    duration: "12:15",
    youtubeId: "YidRfID9rvc",
    youtubeUrl: "https://www.youtube.com/watch?v=YidRfID9rvc",
    thumbnail: "https://i.ytimg.com/vi/YidRfID9rvc/hqdefault.jpg",
    desc: "Explicación médica clara en español sobre los factores de riesgo de la enfermedad cardiovascular, síntomas atípicos y prevención en la comunidad hispana."
  },
  {
    id: "v_4",
    title: "Cáncer de Mama: Diálogo, Diagnóstico Temprano y Prevención",
    category: "Prevención de Cáncer",
    speaker: "Mayo Clinic en Español & Especialistas en Oncología",
    views: "94,000 vistas",
    duration: "09:07",
    youtubeId: "rlpN249ucDI",
    youtubeUrl: "https://www.youtube.com/watch?v=rlpN249ucDI",
    thumbnail: "https://i.ytimg.com/vi/rlpN249ucDI/hqdefault.jpg",
    desc: "Conversación especializada sobre la importancia de las mamografías periódicas, detección oportuna y tratamientos avanzados para la salud femenina."
  },
  {
    id: "v_5",
    title: "Control de la Diabetes Tipo 2: Consejos de Ejercicio y Alimentación",
    category: "Enfermedades Crónicas",
    speaker: "Noticias Telemundo & Endocrinología",
    views: "86,000 vistas",
    duration: "07:30",
    youtubeId: "oARWA-ebyhU",
    youtubeUrl: "https://www.youtube.com/watch?v=oARWA-ebyhU",
    thumbnail: "https://i.ytimg.com/vi/oARWA-ebyhU/hqdefault.jpg",
    desc: "Recomendaciones prácticas sobre horarios óptimos de ejercicio, control de glucosa y hábitos para personas con diabetes tipo 2 e hipertensión."
  }
];

let activeVideoIndex = 0;

function getEmbedUrl(item) {
  if (!item) return '';
  let ytId = item.youtubeId;
  if (!ytId && item.youtubeUrl) {
    const m = item.youtubeUrl.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (m) ytId = m[1];
  }
  if (ytId) {
    return `https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0&modestbranding=1`;
  }
  return item.videoUrl || item.videoFile || '';
}

async function initVideoPlayer() {
  const activeVideoTitle = document.getElementById('activeVideoTitle');
  const activeVideoDesc = document.getElementById('activeVideoDesc');
  const activeVideoSpeaker = document.getElementById('activeVideoSpeaker');
  const videoScreen = document.getElementById('videoScreen');
  const catButtons = document.querySelectorAll('.video-cat-btn');
  const playlistCards = document.querySelectorAll('.playlist-card');

  if (!videoScreen) return;

  // Try fetching latest videos from CMS API
  try {
    const res = await fetch(`/api/videos.php?active_only=1&_t=${Date.now()}`);
    const data = await res.json();
    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
      videoData = data.data.map(v => ({
        id: v.id,
        title: v.title,
        category: v.category,
        speaker: v.doctor || v.speaker || 'Especialista Médico',
        views: v.views || '100K vistas',
        duration: v.duration || '10:00',
        youtubeId: v.youtubeId || '',
        youtubeUrl: v.youtubeUrl || '',
        videoUrl: v.videoUrl || '',
        videoFile: v.videoFile || '',
        thumbnail: v.thumbnail || (v.youtubeId ? `https://i.ytimg.com/vi/${v.youtubeId}/hqdefault.jpg` : ''),
        desc: v.summary || v.description || ''
      }));
    }
  } catch (e) {}

  function playVideo(index) {
    activeVideoIndex = index;
    const cur = videoData[index] || videoData[0];
    if (!cur) return;

    if (activeVideoTitle) activeVideoTitle.textContent = cur.title;
    if (activeVideoDesc) activeVideoDesc.textContent = cur.desc;
    if (activeVideoSpeaker) activeVideoSpeaker.innerHTML = `${cur.speaker} · 👁️ ${cur.views}`;

    const embedUrl = getEmbedUrl(cur);
    if (embedUrl.includes('youtube.com') || embedUrl.includes('youtu.be')) {
      videoScreen.innerHTML = `
        <div style="position:relative; width:100%; height:100%; background:#000;">
          <iframe src="${embedUrl}" title="${escapeHtml(cur.title)}" 
            style="width:100%; height:100%; border:none;" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
            allowfullscreen>
          </iframe>
        </div>
      `;
    } else if (embedUrl) {
      videoScreen.innerHTML = `
        <div style="position:relative; width:100%; height:100%; background:#000;">
          <video src="${embedUrl}" style="width:100%; height:100%; object-fit:cover;" controls autoplay playsinline></video>
        </div>
      `;
    }
  }

  videoScreen.addEventListener('click', () => {
    playVideo(activeVideoIndex);
  });

  catButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      catButtons.forEach(b => b.style.background = 'rgba(255,255,255,0.1)');
      btn.style.background = 'var(--color-news-red)';
      const cat = btn.getAttribute('data-cat');
      
      playlistCards.forEach((card, idx) => {
        const itemCat = card.getAttribute('data-cat');
        if (cat === 'all' || cat === 'Todos' || itemCat === cat) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  playlistCards.forEach((card, index) => {
    card.addEventListener('click', () => {
      playlistCards.forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      playVideo(index);
    });
  });
}

// Modals Manager
function initModals() {
  const modalBackdrop = document.getElementById('modalBackdrop');
  const modalTitle = document.getElementById('modalTitle');
  const modalBody = document.getElementById('modalBody');
  const modalCloseBtn = document.getElementById('modalCloseBtn');

  if (!modalBackdrop) return;

  window.openModal = function(title, contentHtml) {
    modalTitle.textContent = title;
    modalBody.innerHTML = contentHtml;
    modalBackdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeModal = function() {
    modalBackdrop.classList.remove('open');
    document.body.style.overflow = 'auto';
  };

  if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', closeModal);
  }

  modalBackdrop.addEventListener('click', (e) => {
    if (e.target === modalBackdrop) {
      closeModal();
    }
  });
}

// Calculators & Interactive Patient Tools
function initCalculators() {
  window.openSubsidyCalculator = function() {
    const html = `
      <div class="widget-box">
        <p style="font-size:0.85rem; color:#475569; margin-bottom:1rem;">
          Calcule el subsidio estimado de crédito fiscal para la prima del seguro ACA (Obamacare) en Nueva Jersey según sus ingresos familiares.
        </p>
        <div class="form-group">
          <label class="form-label">Número de personas en el hogar:</label>
          <select id="calcFamilySize" class="form-select">
            <option value="1">1 Persona</option>
            <option value="2">2 Personas</option>
            <option value="3">3 Personas</option>
            <option value="4">4 Personas</option>
            <option value="5">5+ Personas</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Ingreso Anual Estimado del Hogar ($ USD):</label>
          <input type="number" id="calcIncome" class="form-input" placeholder="Ej. 32000" value="28000">
        </div>
        <button onclick="runSubsidyCalc()" class="btn-news-red" style="width:100%; justify-content:center; margin-top:0.5rem;">
          Calcular Subsidio Estimado
        </button>
        <div id="calcOutput" class="calc-result" style="display:none; margin-top:1rem; padding:1rem; background:#f9fafb; border:1px solid #cbd5e1;">
          <p style="font-size:0.75rem; color:#c91818; font-weight:800; text-transform:uppercase;">Subsidio Mensual Estimado</p>
          <div id="calcAmount" style="font-size:1.5rem; font-weight:900; color:#111111;">$420 / mes</div>
          <p id="calcNote" style="font-size:0.8rem; color:#475569; margin-top:0.3rem;">¡Califica para primas de seguro de $0 a $30/mes!</p>
        </div>
      </div>
    `;
    openModal('🧮 Calculadora de Subsidios ACA Obamacare 2026', html);
  };

  window.runSubsidyCalc = function() {
    const familySize = parseInt(document.getElementById('calcFamilySize').value) || 1;
    const income = parseFloat(document.getElementById('calcIncome').value) || 0;
    const output = document.getElementById('calcOutput');
    const amount = document.getElementById('calcAmount');
    const note = document.getElementById('calcNote');

    const baseFpl = 15060 + (familySize - 1) * 5380;
    const fplPercentage = (income / baseFpl) * 100;

    output.style.display = 'block';

    if (income <= baseFpl * 1.38) {
      amount.textContent = "NJ FamilyCare / Medicaid";
      note.textContent = "Sus ingresos califican para cobertura de salud GRATUITA a través del programa NJ FamilyCare.";
    } else if (fplPercentage <= 400) {
      const estimatedSubsidy = Math.max(150, Math.round((400 - fplPercentage) * 2.2 + familySize * 65));
      amount.textContent = `$${estimatedSubsidy} / mes`;
      note.textContent = `Califica para el Crédito Fiscal NJ HealthLink. Su costo de prima mensual será significativamente reducido.`;
    } else {
      amount.textContent = "Coordinación Especial";
      note.textContent = "Puede calificar para planes plata con reducción de costos compartidos o deducciones fiscales.";
    }
  };

  window.openInsuranceMatcher = function() {
    const html = `
      <div class="widget-box">
        <p style="font-size:0.85rem; color:#475569; margin-bottom:1rem;">
          Responda 3 preguntas rápidas para determinar si califica para Medicare, Medicaid o ACA Obamacare en NJ.
        </p>
        <div class="form-group">
          <label class="form-label">1. Su edad actual:</label>
          <select id="matchAge" class="form-select">
            <option value="under65">Menor de 65 años</option>
            <option value="65over">65 años o más</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">2. Estatus de Residencia / Ciudadanía:</label>
          <select id="matchStatus" class="form-select">
            <option value="citizen">Ciudadano / Residente Permanente (5+ años)</option>
            <option value="new_resident">Residente Reciente / Visa de Trabajo</option>
            <option value="other">Otros Estatus / Consulta Confidencial</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">3. Rango de Ingresos del Hogar:</label>
          <select id="matchIncomeRange" class="form-select">
            <option value="low">Menos de $20,000 / año</option>
            <option value="mid">$20,000 - $55,000 / año</option>
            <option value="high">Más de $55,000 / año</option>
          </select>
        </div>
        <button onclick="runInsuranceMatcher()" class="btn-news-red" style="width:100%; justify-content:center; margin-top:0.5rem;">
          Diagnosticar Cobertura Recomendada
        </button>
        <div id="matchOutput" class="calc-result" style="display:none; margin-top:1rem; padding:1rem; background:#f9fafb; border:1px solid #cbd5e1;">
          <div id="matchTitle" style="font-size:1.05rem; font-weight:800; color:#c91818; margin-bottom:0.25rem;"></div>
          <div id="matchDesc" style="font-size:0.825rem; color:#334155; line-height:1.5;"></div>
        </div>
      </div>
    `;
    openModal('🏥 Diagnosticador de Cobertura de Salud', html);
  };

  window.runInsuranceMatcher = function() {
    const age = document.getElementById('matchAge').value;
    const status = document.getElementById('matchStatus').value;
    const income = document.getElementById('matchIncomeRange').value;
    const output = document.getElementById('matchOutput');
    const title = document.getElementById('matchTitle');
    const desc = document.getElementById('matchDesc');

    output.style.display = 'block';

    if (age === '65over') {
      title.textContent = "🎯 Cobertura Recomendada: Medicare Partes A, B y D / Medicare Advantage";
      desc.textContent = "Es elegible para Medicare. Le recomendamos verificar si califica para los programas de ayuda adicional (Extra Help / MSP) para cubrir primas y medicamentos.";
    } else if (income === 'low' && status === 'citizen') {
      title.textContent = "🎯 Cobertura Recomendada: NJ FamilyCare (Medicaid de NJ)";
      desc.textContent = "Tiene derecho a atención médica gratuita sin costo de prima a través de NJ FamilyCare. Incluye médicos, hospital y recetas.";
    } else {
      title.textContent = "🎯 Cobertura Recomendada: Plan de Salud ACA con Subsidio Estatal Get Covered NJ";
      desc.textContent = "Califica para planes de salud privados con importantes subsidios gubernamentales que reducen su cuota mensual a precios muy accesibles.";
    }
  };

  window.openMedicalDict = function() {
    const html = `
      <div class="widget-box">
        <p style="font-size:0.85rem; color:#475569; margin-bottom:0.75rem;">
          Busque términos médicos comunes en inglés con su traducción exacta y explicación en español.
        </p>
        <input type="text" id="dictSearch" onkeyup="filterDict()" class="form-input" placeholder="Buscar término (ej. Out-of-pocket, Deductible, Prescription, Referral)..." style="margin-bottom:1rem;">
        
        <div id="dictList" style="display:flex; flex-direction:column; gap:0.75rem; max-height:350px; overflow-y:auto;">
          <div class="dict-item" style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:0.75rem;">
            <strong style="color:#c91818;">Deductible (Deducible)</strong>
            <p style="font-size:0.8rem; color:#475569; margin-top:2px;">Monto anual que debe pagar de su bolsillo antes de que el seguro comience a cubrir sus gastos médicos.</p>
          </div>
          <div class="dict-item" style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:0.75rem;">
            <strong style="color:#c91818;">Copayment / Copay (Copago)</strong>
            <p style="font-size:0.8rem; color:#475569; margin-top:2px;">Tarifa fija que usted paga en el momento de recibir un servicio médico o comprar un medicamento (ej. $15 por consulta).</p>
          </div>
          <div class="dict-item" style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:0.75rem;">
            <strong style="color:#c91818;">Out-of-Pocket Maximum (Límite Máximo de Bolsillo)</strong>
            <p style="font-size:0.8rem; color:#475569; margin-top:2px;">Lo máximo que pagará en un año por servicios cubiertos. Después de alcanzar esta cifra, el seguro paga el 100%.</p>
          </div>
          <div class="dict-item" style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:0.75rem;">
            <strong style="color:#c91818;">Referral (Referencia / Volante Médico)</strong>
            <p style="font-size:0.8rem; color:#475569; margin-top:2px;">Autorización de su médico primario para acudir a un especialista o realizarse un estudio avanzado.</p>
          </div>
          <div class="dict-item" style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:0.75rem;">
            <strong style="color:#c91818;">In-Network Provider (Proveedor de la Red)</strong>
            <p style="font-size:0.8rem; color:#475569; margin-top:2px;">Médicos u hospitales que tienen contrato con su plan de seguro para ofrecer tarifas con descuento.</p>
          </div>
        </div>
      </div>
    `;
    openModal('📖 Diccionario de Términos Médicos Inglés-Español', html);
  };

  window.filterDict = function() {
    const q = document.getElementById('dictSearch').value.toLowerCase();
    const items = document.querySelectorAll('#dictList .dict-item');
    items.forEach(item => {
      const text = item.textContent.toLowerCase();
      item.style.display = text.includes(q) ? 'block' : 'none';
    });
  };
}

// News Search/Filter
function initNewsFilter() {
  const newsInput = document.getElementById('newsSearchInput');
  if (!newsInput) return;

  newsInput.addEventListener('keyup', () => {
    const term = newsInput.value.toLowerCase();
    const cards = document.querySelectorAll('.news-card');
    cards.forEach(card => {
      const title = card.querySelector('.card-head').textContent.toLowerCase();
      const desc = card.querySelector('.card-body').textContent.toLowerCase();
      if (title.includes(term) || desc.includes(term)) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  });
}
