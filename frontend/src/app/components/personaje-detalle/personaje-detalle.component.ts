import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { ApiService } from '../../services/api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-personaje-detalle',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './personaje-detalle.component.html',
  styleUrl: './personaje-detalle.component.scss'
})
export class PersonajeDetalleComponent {
  personaje: any = null;
  loading = true;
  importando = false;
  mensajeImportacion = '';
  episodiosCompletos: any[] = [];

  constructor(private api: ApiService, private route: ActivatedRoute, private router: Router) {
    this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.api.getPersonajeDetalleApi(id).subscribe({
          next: (data: any) => {
            this.personaje = data;
            console.log('Personaje recibido:', data);
            // Obtener IDs de episodios
            const ids = Array.isArray(data.episode) && data.episode !== null ? data.episode.map((url: string) => parseInt(url.split('/').pop() || '')) : [];
            console.log('IDs de episodios:', ids);
            if (ids.length) {
              this.api.getEpisodiosApiPorIds(ids).subscribe({
                next: (eps) => {
                  this.episodiosCompletos = Array.isArray(eps) ? eps : [eps];
                  console.log('Episodios completos:', this.episodiosCompletos);
                },
                error: () => this.episodiosCompletos = []
              });
            } else {
              this.episodiosCompletos = [];
            }
            this.loading = false;
          },
          error: () => this.loading = false
        });
      }
    });
  }

  importar() {
    if (!this.personaje) {
      console.log('No hay personaje para importar');
      return;
    }
    
    console.log('=== INICIANDO IMPORTACIÓN ===');
    console.log('Personaje:', this.personaje);
    console.log('Episodios cargados:', this.episodiosCompletos);
    
    this.importando = true;
    this.mensajeImportacion = '';

    // 1. Mapear datos del personaje
    const personajeData = {
      id: this.personaje.id,
      nombre: this.personaje.name,
      estado: this.personaje.status || 'Unknown',
      especie: this.personaje.species || 'Unknown',
      tipo: this.personaje.type || '',
      genero: this.personaje.gender || 'Unknown',
      imagen: this.personaje.image || ''
    };

    console.log('Datos del personaje:', personajeData);

    // 2. Mapear datos de la locación (origen del personaje)
    let locacionData = {
      id: 0,
      nombre: 'Unknown',
      tipo: 'Unknown',
      dimension: 'Unknown'
    };

    if (this.personaje.origin && this.personaje.origin.name && this.personaje.origin.name !== 'unknown') {
      // Extraer ID de la URL si existe
      let locacionId = 0;
      if (this.personaje.origin.url && this.personaje.origin.url !== '') {
        const urlParts = this.personaje.origin.url.split('/');
        const lastPart = urlParts[urlParts.length - 1];
        locacionId = parseInt(lastPart) || 0;
      }
      
      locacionData = {
        id: locacionId > 0 ? locacionId : 999, // ID por defecto si no se puede extraer
        nombre: this.personaje.origin.name,
        tipo: 'Unknown',
        dimension: 'Unknown'
      };
    }

    console.log('Datos de la locación:', locacionData);

    // 3. Mapear datos de los episodios
    const episodiosData = (this.episodiosCompletos || [])
      .filter((ep: any) => ep && ep.id && ep.name && ep.air_date && ep.episode && ep.url)
      .map((ep: any) => {
        // Convertir fecha de "January 27, 2014" a "2014-01-27"
        let fechaFormateada = '2000-01-01'; // fecha por defecto
        if (ep.air_date) {
          console.log(`Procesando fecha original: "${ep.air_date}"`);
          try {
            const fecha = new Date(ep.air_date);
            if (!isNaN(fecha.getTime())) {
              fechaFormateada = fecha.toISOString().slice(0, 10); // YYYY-MM-DD
              console.log(`Fecha formateada: "${fechaFormateada}"`);
            } else {
              console.warn(`Fecha inválida: "${ep.air_date}"`);
            }
          } catch (error) {
            console.warn('Error al formatear fecha:', ep.air_date, error);
          }
        }
        
        return {
          id: ep.id,
          nombre: ep.name,
          fecha_emision: fechaFormateada,
          codigo: ep.episode,
          url: ep.url
        };
      });

    console.log('Datos de episodios:', episodiosData);

    // 4. Validar que tengamos los datos mínimos requeridos
    if (!personajeData.nombre || episodiosData.length === 0) {
      console.log('VALIDACIÓN FALLIDA:');
      console.log('- Nombre del personaje:', personajeData.nombre);
      console.log('- Cantidad de episodios:', episodiosData.length);
      this.importando = false;
      this.mensajeImportacion = 'Faltan datos requeridos: nombre del personaje y al menos un episodio.';
      return;
    }

    // 5. Preparar datos para enviar al backend
    const dataToSend = {
      personaje: personajeData,
      locacion: locacionData,
      episodios: episodiosData
    };

    console.log('Datos finales a enviar:', dataToSend);

    // 6. Enviar al backend
    this.api.importarPersonaje(dataToSend).subscribe({
      next: (response) => {
        console.log('Importación exitosa:', response);
        this.importando = false;
        this.mensajeImportacion = '¡Importación exitosa!';
      },
      error: (error) => {
        console.error('Error en importación:', error);
        this.importando = false;
        this.mensajeImportacion = 'Error al importar. Revisa la consola para más detalles.';
      }
    });
  }
}
