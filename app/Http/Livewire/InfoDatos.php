<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Personaje;
use App\Models\Episodio;
use App\Models\Localizacion;
use App\Models\Origen;

class InfoDatos extends Component
{
    /**
     * Se crea una variable global para consultar los datos de los personajes creados
     */
    public $arrData;


    /**
     * Se realiza un listener para realizar el llamado a la funcion 'setDatosPersonajes', 'getDatosPerso', 'updateDatosPerso'
     */
    protected $listeners = ['setDatosPersonajes', 'getDatosPerso', 'updateDatosPerso'];

    /**
     * Se crea un a función para hacer la creación del personaje, esta inforamción se registra en la tabla personajes
     */
    public function setDatosPersonajes($data)
    {

        $vali = Personaje::where('borrado', 1)
            ->with('localizacion')
            ->get();

        if(count($vali) > 0){
            $this->dispatchBrowserEvent('setDatosPersonajes', "FAIL");
        }else{
            foreach ($data as $d) {
                $arrEpis = $d["episode"];
                $arrLoca = $d["location"];
                $arrOrig = $d["origin"];
    
                $pers = new Personaje;
    
                $pers->nombre = $d["name"];
                $pers->estado = $d["status"];
                $pers->especie = $d["species"];
                $pers->imagen = $d["image"];
                $pers->tipo = $d["type"];
                $pers->genero = $d["gender"];
    
                $pers->save();
    
    
                $this->setDatosAdicionales($arrEpis, $arrLoca, $arrOrig, $pers->id);

                $this->dispatchBrowserEvent('setDatosPersonajes', "OK");
            }
        }
    }

    /**
     * Función creada para la creación de los datos adicionales por personaje
     */
    public function setDatosAdicionales($arrEpis, $arrLoca, $arrOrig, $persId)
    {

        /**
         * Se crea un foreach para acceder a la URL  de los episodios por personaje y se registra en la
         * tabla de episodios
         */
        foreach ($arrEpis as $e) {
            $epis = new Episodio;

            $epis->pers_id = $persId;
            $epis->url = $e;

            $epis->save();
        }

        /**
         * Se crea el script para hacer registrar la localización del personaje en la tabla localizacion
         */
        $loca = new Localizacion;

        $loca->pers_id = $persId;
        $loca->nomb_loca = $arrLoca["name"];
        $loca->url = $arrLoca["url"];

        $loca->save();

        /**
         * Se crea el script para hacer registrar el origen del personaje en la tabla origen
         */
        $orig = new Origen;

        $orig->pers_id = $persId;
        $orig->nomb_orig = $arrOrig["name"];
        $orig->url = $arrOrig["url"];

        $orig->save();
    }

    /**
     * Se crea una función la cual llama los datos especificos del personaje seleccionado
     */
    public function getDatosPerso($id)
    {
        $pers = Personaje::where('borrado', 1)
            ->where('id', $id)
            ->with('localizacion')
            ->get();

        $this->dispatchBrowserEvent('getDatosPerso', $pers);
    }

    /**
     * Se crea una función para actualizar los datos del personaje seleccionado
     */
    public function updateDatosPerso($arrDatos)
    {
        Localizacion::where('pers_id', $arrDatos["id"])
            ->update(['nomb_loca' => $arrDatos["local"]]);
            

        $rest = Personaje::where('id', $arrDatos["id"])
            ->update(['estado' => $arrDatos["estado"], 'especie' => $arrDatos["especie"], 'genero' => $arrDatos["genero"], 'tipo' => $arrDatos["tipo"]]);

        if($rest){
            $this->dispatchBrowserEvent('updateDatosPerso', "OK");
        }else{
            $this->dispatchBrowserEvent('updateDatosPerso', "FAIL");
        }
    }

    public function render()
    {

        $this->arrData = Personaje::where('borrado', 1)
            ->with('localizacion')
            ->get();

        return <<<'blade'
            <div>
                <div class="container row">
                    <div class="col-md-12" id="contList">
                        <div class="col-md-12 mt-3 mb-3">
                            <button type="button" class="btn btn-primary" id="btnAgregar">Agregar datos</button>
                        </div>
                        @if(count($arrData) > 0)
                            <div class="col-md-12">
                                <p class="fs-2">Lista de personaje</p>
                            </div>
                            <div class="row">
                                @foreach($arrData as $d)
                                    <div class="col-md-4 col-sm-6 mt-2 mb-2">
                                        <div class="card" style="width: 18rem; margin: 0 auto;">
                                            <img src="{{$d->imagen}}" class="card-img-top img-fluid" alt="{{$d->nombre}}">
                                            <div class="card-body">
                                                <h5 class="card-title">{{$d->nombre}}</h5>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                @php
                                                    if($d->estado == "Alive"){
                                                        $color = 'text-success';
                                                    }else if($d->estado == "Dead"){
                                                        $color = 'text-danger';
                                                    }else{
                                                        $color = 'text-muted';
                                                    }
                                                @endphp
                                                <li class="list-group-item"><i class="fa-solid fa-circle {{$color}}"></i> {{$d->estado}}</li>
                                                <li class="list-group-item">{{$d->especie}}</li>
                                                <li class="list-group-item">{{$d->localizacion->nomb_loca}}</li>
                                            </ul>
                                            <div class="card-body text-center">
                                                <button type="button" class="btn btn-success" wire:click="$emit('getDatosPerso', {{$d->id}})">Editar <i class="fa-solid fa-pen-to-square"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="col-md-12 text-center">
                                <p class="fs-3 text-danger">¡NO SE HAN ENCONTRADO REGISTROS!</p>
                                <br/>
                                <p class="fs-3 text-danger">Por favor de click en el botón agregar datos.</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-12" id="contEdit" style="display: none;">
                        <div class="col-md-12 mt-3 mb-3">
                            <button type="button" class="btn btn-info" id="btnRegre"><i class="fa-solid fa-arrow-left"></i> Regresar</button>
                        </div>
                        <div class="col-md-12" id="contDeta">
                        </div>
                    </div>
                </div>
                @push('script-info')
                    <script>
                        document.addEventListener('livewire:load', function () {
                            $('#carouselExampleControls').carousel();

                            $("#btnAgregar").click(function(){
                                $.ajax({  
                                    type: "GET",  
                                    url: "https://rickandmortyapi.com/api/character",    
                                    dataType: "json", 
                                    success: function (data) {
                                        Livewire.emit('setDatosPersonajes', data.results);
                                        window.addEventListener('setDatosPersonajes', function(e){
                                            if(e.detail == "OK"){
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: '¡PERFECTO!',
                                                    text: 'Se crearon correctamente los datos.',
                                                    showConfirmButton: false,
                                                    timer: 2000
                                                });

                                                $("#contList").show("slow");
                                                $("#contEdit").hide("slow");
                                            }else{
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: '¡UN MOMENTO!',
                                                    text: 'Los datos de los personajes ya se encuentran cargados.',
                                                    showConfirmButton: false,
                                                    timer: 2000
                                                });
                                            }
                                        });
                                    },
                                });
                            });

                            $("#btnRegre").click(function(){
                                $("#contList").show("slow");
                                $("#contEdit").hide("slow");
                            });

                            window.addEventListener('getDatosPerso', function(e){
                                if(e.detail.length > 0){
                                    $("#contList").hide("slow");
                                    $("#contEdit").show("slow");
                                    $("#contDeta").empty();
                                    $("#contDeta").append(`
                                        <div class="col-md-12">
                                            <div class="bd-example">
                                                <div class="card mb-3 col-md-12">
                                                    <div class="row g-0">
                                                        <div class="col-md-4">
                                                            <img src="${e.detail[0]["imagen"]}" class="card-img-top img-fluid" alt="${e.detail[0]["nombre"]}">
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="card-body row">
                                                                <div class="col-md-6 col-sm-12">
                                                                    <label for="estado" class="form-label">Estado</label>
                                                                    <select class="form-select" aria-label="Default select example" id="estado">
                                                                        <option value="Alive" ${e.detail[0]["estado"] == "Alive" ? 'selected': ''}>Alive</option>
                                                                        <option value="Dead" ${e.detail[0]["estado"] == "Dead" ? 'selected': ''}>Dead</option>
                                                                        <option value="unknown" ${e.detail[0]["estado"] == "unknown" ? 'selected': ''}>unknown</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 col-sm-12">
                                                                    <label for="especie" class="form-label">Especie</label>
                                                                    <input type="text" class="form-control" id="especie" value="${e.detail[0]["especie"]}">
                                                                </div>
                                                                <div class="col-md-6 col-sm-12">
                                                                    <label for="tipo" class="form-label">Tipo</label>
                                                                    <input type="text" class="form-control" id="tipo" value="${e.detail[0]["tipo"]}">
                                                                </div>
                                                                <div class="col-md-6 col-sm-12">
                                                                    <label for="genero" class="form-label">Genero</label>
                                                                    <select class="form-select" aria-label="Default select example" id="genero">
                                                                        <option value="Male" ${e.detail[0]["genero"] == "Male" ? 'selected': ''}>Male</option>
                                                                        <option value="Female" ${e.detail[0]["genero"] == "Female" ? 'selected': ''}>Female</option>
                                                                        <option value="unknown" ${e.detail[0]["genero"] == "unknown" ? 'selected': ''}>unknown</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-12 col-sm-12">
                                                                    <label for="loca" class="form-label">Localización</label>
                                                                    <input type="text" class="form-control" id="loca" value="${e.detail[0]["localizacion"]["nomb_loca"]}">
                                                                </div>
                                                                <div class="col-md-12 text-end mt-5">
                                                                    <button id="btnActualizar" type="button" class="btn btn-success" data-id="${e.detail[0]["id"]}">Guardar <i class="fa-solid fa-floppy-disk"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `);

                                    $("#btnActualizar").click(function(){
                                        var arrDatos = {
                                            "id": $(this).data('id'),
                                            "estado": $("#estado").val(),
                                            "especie": $("#especie").val(),
                                            "tipo": $("#tipo").val(),
                                            "genero": $("#genero").val(),
                                            "local": $("#loca").val()
                                        };
                                        
                                        Livewire.emit('updateDatosPerso', arrDatos);
                                        window.addEventListener('updateDatosPerso', function(e){
                                            if(e.detail == "OK"){
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: '¡PERFECTO!',
                                                    text: 'Se actualizaron los datos correctamente',
                                                    showConfirmButton: false,
                                                    timer: 2000
                                                });

                                                $("#contList").show("slow");
                                                $("#contEdit").hide("slow");
                                            }else{
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: '¡UN MOMENTO!',
                                                    text: 'Ocurrio un error al actualizar los datos',
                                                    showConfirmButton: false,
                                                    timer: 2000
                                                });
                                            }
                                        });
                                    });
                                }else{
                                    Swal.fire({
                                        icon: 'error',
                                        title: '¡UN MOMENTO!',
                                        text: 'Error al consultar los datos del personaje',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                }
                            });
                        });
                    </script>
                @endpush
            </div>
        blade;
    }
}
