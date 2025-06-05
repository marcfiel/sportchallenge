<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

class NoticiasController extends Controller
{
    public function mostrarNoticia($id)
    {
        $json = Storage::get('noticias.json');
        $noticias = json_decode($json, true);
        $noticia = null;

        // Buscar la noticia en el array usando el id
        foreach ($noticias as $item) {
            if ($item['id'] == $id) {
                $noticia = $item;
                break;
            }
        }

        // Si no se encuentra la noticia, se lanza error 404
        if (!$noticia) {
            abort(404, 'Noticia no encontrada');
        }

        // Pasar la noticia a la vista
        return view('noticia', compact('noticia'));
    }
}
