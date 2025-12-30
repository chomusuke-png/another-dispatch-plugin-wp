<?php
/**
 * Partial: Estilos CSS compartidos para todos los emails.
 * Se incluye dentro de la etiqueta <head> de las plantillas.
 */
?>
<style>
    /* --- Reset & Base --- */
    body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #555555; line-height: 1.6; }
    img { max-width: 100%; height: auto; display: block; }
    a { color: #2271b1; text-decoration: none; }
    
    /* --- Estructura --- */
    .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f4; padding-bottom: 40px; }
    .container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    
    /* --- Header --- */
    .header { background-color: #2271b1; padding: 30px 20px; text-align: center; }
    .header h1 { margin: 0; color: #ffffff; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .header a { color: #ffffff; text-decoration: none; }

    /* --- Subtítulos (Contexto) --- */
    .email-meta { background-color: #e5f6ff; color: #005b99; padding: 15px 20px; text-align: center; font-size: 15px; font-weight: 600; border-bottom: 1px solid #cceeff; }

    /* --- Contenido Principal --- */
    .content-body { padding: 30px; }
    
    /* Títulos de Post */
    .post-title { margin: 0 0 15px 0; font-size: 22px; line-height: 1.3; color: #333333; }
    .post-title a { color: #333333; text-decoration: none; }
    
    /* Bloque de Post (Usado en Digest) */
    .post-item { margin-bottom: 30px; border-bottom: 1px solid #eeeeee; padding-bottom: 30px; }
    .post-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .post-thumb { width: 100%; border-radius: 4px; margin-bottom: 15px; object-fit: cover; }
    
    /* Texto del contenido */
    .post-content, .post-excerpt { font-size: 16px; color: #555555; margin-bottom: 20px; }
    
    /* --- Botones --- */
    .btn-primary { display: inline-block; padding: 12px 24px; background-color: #2271b1; color: #ffffff !important; border-radius: 4px; font-weight: bold; text-align: center; margin-top: 10px; }
    .btn-primary:hover { background-color: #135e96; }
    
    .btn-link { display: inline-block; font-size: 14px; font-weight: bold; color: #2271b1; margin-top: 5px; }
    .btn-link:hover { text-decoration: underline; }

    /* --- Footer --- */
    .footer { background-color: #f4f4f4; text-align: center; padding: 20px; font-size: 12px; color: #999999; border-top: 1px solid #eaeaea; }
    .footer p { margin: 5px 0; }
    .footer a { color: #777777; text-decoration: underline; }
</style>