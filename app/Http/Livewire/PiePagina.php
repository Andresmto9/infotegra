<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PiePagina extends Component
{
    public function render()
    {
        return <<<'blade'
            <div>
                <footer class="bg-dark text-center text-white">
                    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
                        © 2023 Copyright:
                        <a class="text-white">Lisandro Zubieta</a>
                    </div>
                </footer>
            </div>
        blade;
    }
}
