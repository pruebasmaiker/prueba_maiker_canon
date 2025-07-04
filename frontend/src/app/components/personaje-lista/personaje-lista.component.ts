import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { BusquedaComponent } from '../busqueda/busqueda.component';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-personaje-lista',
  standalone: true,
  imports: [CommonModule, BusquedaComponent, RouterModule],
  templateUrl: './personaje-lista.component.html',
  styleUrl: './personaje-lista.component.scss'
})
export class PersonajeListaComponent {
  personajes: any[] = [];
  loading = false;

  constructor(private api: ApiService) {
    this.buscar(); // Cargar todos los personajes al iniciar
  }

  buscar(nombre?: string) {
    this.loading = true;
    this.api.getPersonajesApi(nombre).subscribe({
      next: (data: any) => {
        this.personajes = data.results || [];
        this.loading = false;
      },
      error: () => {
        this.personajes = [];
        this.loading = false;
      }
    });
  }
}