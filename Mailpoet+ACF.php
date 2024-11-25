// Función para modificar el contenido y agregar campos personalizados de ACF
add_filter('mailpoet_newsletter_shortcode', 'mailpoet_custom_shortcode', 10, 6);

function mailpoet_custom_shortcode($shortcode, $newsletter, $subscriber, $queue, $newsletter_body, $arguments) {
  // Si el shortcode no coincide con el que deseas crear, devuelve el shortcode original
  if (strpos($shortcode, '[custom:latest_articles') === false) return $shortcode;

  // Obtener el número de posts especificado en el shortcode
  $num_posts = 5; // Valor predeterminado
  if (preg_match('/\[custom:latest_articles\s+(\d+)\]/', $shortcode, $matches)) {
	$num_posts = intval($matches[1]); // Convertir el número de posts a entero
  }

  // Crear el contenido personalizado que deseas incluir en el newsletter
  $contenido_personalizado = '';

  // Obtener la fecha y hora actual
  $fecha_hora_actual = current_time('YmdHis'); // Formato: YYYYMMDDHHMMSS

  // Obtener los últimos artículos de la categoría "agenda"
  $args = array(
	'post_type' => 'agenda',
	'posts_per_page' => $num_posts, // Usar el número de posts especificado
	'meta_key' => 'date_et_heure', // Campo personalizado de fecha del evento ACF
	'orderby' => 'meta_value',
	'order' => 'ASC', // Puedes cambiar a 'DESC' si prefieres ordenar en sentido descendente
	'meta_query' => array(
	  array(
		'key' => 'date_et_heure',
		'value' => $fecha_hora_actual,
		'compare' => '>=', // Solo obtener eventos que son en el futuro
		'type' => 'DATETIME' // Asegúrate de que la comparación se haga como una fecha y hora
	  )
	)
  );
  
  $latest_posts = new WP_Query($args);

  // Comprobar si hay artículos disponibles
  if ($latest_posts->have_posts()) {
	while ($latest_posts->have_posts()) {
	  $latest_posts->the_post();
	  
	  // Obtener datos del artículo
	  $titulo = get_the_title();
	  $imagen = get_the_post_thumbnail_url($post->ID, 'large'); // Obtener la URL de la imagen en tamaño grande
	  $extracto = get_the_excerpt();
	  $enlace = get_permalink();

	  // Obtener la fecha y hora del evento
		$fecha_hora_evento = get_field('date_et_heure', false, false);
		if ($fecha_hora_evento) {
		  $fecha_hora_objeto = new DateTime($fecha_hora_evento);
  
		  // Formatear la fecha y la hora
		  $mes_frances = [
			1 => 'janvier',
			2 => 'février',
			3 => 'mars',
			4 => 'avril',
			5 => 'mai',
			6 => 'juin',
			7 => 'juillet',
			8 => 'août',
			9 => 'septembre',
			10 => 'octobre',
			11 => 'novembre',
			12 => 'décembre'
		  ];
  
		  $mes_numero = $fecha_hora_objeto->format('n');
		  $mes_formateado = $mes_frances[$mes_numero];
  
		  $fecha_formateada = $fecha_hora_objeto->format('j') . ' ' . $mes_formateado . ' ' . $fecha_hora_objeto->format('Y');
		  $hora_formateada = $fecha_hora_objeto->format('G\hi');
		  $hora_formateada = str_replace(':', 'h', $hora_formateada);
		} else {
		  $fecha_formateada = '';
		  $hora_formateada = '';
		}
	  
	  // Construir el contenido personalizado
	  $contenido_personalizado .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">';
	  $contenido_personalizado .= '<tr>';
	  
	  // Primera columna (imagen)
	  $contenido_personalizado .= '<td width="50%" style="padding-right: 10px; vertical-align: top;">';
	  if ($imagen) {
		$contenido_personalizado .= '<img src="' . $imagen . '" alt="' . $titulo . '" style="max-width: 100%; height: auto; display: block;">';
	  }
	  $contenido_personalizado .= '</td>';
	  
	  // Segunda columna (contenido)
	  $contenido_personalizado .= '<td width="50%" style="padding-left: 20px; vertical-align: top; color:#ffffff;font-family:sans-serif;">';
	  $contenido_personalizado .= '<h2 style="margin-top: 0; margin-bottom: 10px;text-transform:uppercase;font-weight:300;">' . $titulo . '</h2>';
	  
	  // Limitar el extracto a 20 palabras
	  $extracto_limitado = wp_trim_words($extracto, 20);
	  
	  if ($fecha_formateada && $hora_formateada) {
		  $contenido_personalizado .= '<div><strong style="text-decoration:underline;"> ' . $fecha_formateada . ' - ' . $hora_formateada . '</strong></div>';
		}
	  $contenido_personalizado .= '<p style="margin-top: 15px; margin-bottom: 15px; font-size:14px">' . $extracto_limitado . '</p>';
	  $contenido_personalizado .= '<a href="' . $enlace . '" style="color:#ffffff;text-decoration:none;display:inline-block;-webkit-text-size-adjust:none;mso-hide:all;text-align:center;background-color:#6c125b;border-color:#000000;border-width:0px;border-radius:36px;border-style:solid;width:85px;line-height:34px;font-size:12px;font-weight:normal" class="mailpoet_button">+ INFO</a>';
	  $contenido_personalizado .= '</td>';
	  
	  $contenido_personalizado .= '</tr>';
	  $contenido_personalizado .= '</table>';
	  
	  // Agregar estilos responsivos para dispositivos móviles
	  $contenido_personalizado .= '<style type="text/css">';
	  $contenido_personalizado .= '@media only screen and (max-width: 600px) {';
	  $contenido_personalizado .= 'table { width: 100% !important; }';
	  $contenido_personalizado .= 'td { display: block !important; width: 100% !important; padding: 0 !important; }';
	  $contenido_personalizado .= '}'; // Cierre de media query
	  $contenido_personalizado .= '</style>'; // Cierre de etiqueta de estilo
	  
	  // Agregar línea blanca entre cada artículo
	  $contenido_personalizado .= '<hr style="margin: 20px 0;height:0; border-style:solid">';
	}
	wp_reset_postdata();
  } else {
	$contenido_personalizado = '<p>No hay artículos disponibles.</p>';
  }

  // Construir el contenido del shortcode
  $shortcode_content = "<div>";
  $shortcode_content .= $contenido_personalizado; // Agregar el contenido personalizado aquí
  $shortcode_content .= "</div>";

  return $shortcode_content;
}
