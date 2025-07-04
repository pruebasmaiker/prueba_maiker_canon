import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-reportes',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './reportes.component.html',
  styleUrl: './reportes.component.scss'
})
export class ReportesComponent {
  reportePersonajesPorFecha: any[] = [];
  reportePersonajesPorEpisodio: any[] = [];
  reporteLocacionesConPersonajes: any[] = [];
  filtros: any = {
    estado: '',
    locacion: '',
    personaje_nombre: '',
    episodio_nombre: '',
    fecha_inicio: '',
    fecha_fin: ''
  };
  resultadosFlexibles: any = {};
  loading = true;
  loadingFlex: boolean = false;
  estadosDisponibles: string[] = ['Alive', 'Dead', 'unknown'];

  constructor(private api: ApiService) {
    this.cargarReportes();
    this.cargarEstadosDisponibles();
  }

  cargarReportes() {
    this.loading = true;
    this.api.getReportePersonajesPorFecha().subscribe({
      next: (data) => this.reportePersonajesPorFecha = data,
      complete: () => this.loading = false
    });
    this.api.getReportePersonajesPorEpisodio().subscribe({
      next: (data) => this.reportePersonajesPorEpisodio = data
    });
    this.api.getReporteLocacionesConPersonajes().subscribe({
      next: (data) => this.reporteLocacionesConPersonajes = data
    });
  }

  cargarEstadosDisponibles() {
    this.api.getEstadosDisponibles().subscribe({
      next: (estados) => {
        if (estados && estados.length > 0) {
          this.estadosDisponibles = estados;
        }
      }
    });
  }

  buscarReportesFlexibles() {
    this.loadingFlex = true;
    this.api.getReportesFlexibles(this.filtros).subscribe({
      next: (data) => this.resultadosFlexibles = data,
      complete: () => this.loadingFlex = false
    });
  }

  limpiarFiltros() {
    this.filtros = {
      estado: '',
      locacion: '',
      personaje_nombre: '',
      episodio_nombre: '',
      fecha_inicio: '',
      fecha_fin: ''
    };
    this.resultadosFlexibles = {};
  }
}
