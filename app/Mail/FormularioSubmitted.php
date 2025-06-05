<?php
// app/Mail/FormularioSubmitted.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Persona;

class FormularioSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $persona;

    public function __construct(Persona $persona)
    {
        $this->persona = $persona;
    }

    public function build()
    {
        return $this->subject('Registro Completado - Confirmación')
                    ->view('emails.formulario-confirmacion')
                    ->with('persona', $this->persona);
    }
}