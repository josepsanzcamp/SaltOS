<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2022 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

if (!check_user()) {
    action_denied();
}

if ($page == "presupuestos") {
    // BUSCAR DATOS DEL PRESUPUESTO
    $id_presupuesto = abs(intval(getParam("id")));
    $query = "SELECT * FROM tbl_presupuestos WHERE id='$id_presupuesto'";
    $row = execute_query($query);
    if ($row === null) {
        action_denied();
    }
    $id_proyecto = $row["id_proyecto"];
    if ($id_proyecto > 0) {
        session_error(LANG("projectexists", "presupuestos"));
        javascript_history(-1);
        die();
    }
    $id_cliente = $row["id_cliente"];
    if ($id_cliente == 0) {
        session_error(LANG("clientnotexists", "presupuestos"));
        javascript_history(-1);
        die();
    }
    // CREAR PROYECTO
    $id_campanya = $row["id_campanya"];
    $nombre = $row["nombre"];
    $descripcion = $row["descripcion"];
    $query = make_insert_query("tbl_proyectos", array(
        "id_campanya" => $id_campanya,
        "id_cliente" => $id_cliente,
        "nombre" => $nombre,
        "descripcion" => $descripcion
    ));
    db_query($query);
    // OBTENER ID DEL NUEVO PROYECTO
    $query = "SELECT MAX(id) FROM tbl_proyectos";
    $id_proyecto = execute_query($query);
    // AÑADIR CONTROL DEL REGISTRO
    $id_aplicacion = page2id("proyectos");
    $id_usuario = current_user();
    $datetime = current_datetime();
    make_control($id_aplicacion, $id_proyecto);
    make_indexing($id_aplicacion, $id_proyecto);
    // COPIAR LAS TAREAS DEL PRESUPUESTO AL PROYECTO
    $query = "SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto='$id_presupuesto' ORDER BY id ASC";
    $result = execute_query_array($query);
    foreach ($result as $row) {
        $tarea = $row["tarea"];
        $horas = $row["horas"];
        $precio = $row["precio"];
        $descuento = $row["descuento"];
        $query = make_insert_query("tbl_proyectos_t", array(
            "id_proyecto" => $id_proyecto,
            "tarea" => $tarea,
            "horas" => $horas,
            "precio" => $precio,
            "descuento" => $descuento
        ));
        db_query($query);
    }
    // COPIAR LOS PRODUCTOS DEL PRESUPUESTO AL PROYECTO
    $query = "SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto='$id_presupuesto' ORDER BY id ASC";
    $result = execute_query_array($query);
    foreach ($result as $row) {
        $id_producto = $row["id_producto"];
        $concepto = $row["concepto"];
        $unidades = $row["unidades"];
        $precio = $row["precio"];
        $descuento = $row["descuento"];
        $query = make_insert_query("tbl_proyectos_p", array(
            "id_proyecto" => $id_proyecto,
            "id_producto" => $id_producto,
            "concepto" => $concepto,
            "unidades" => $unidades,
            "precio" => $precio,
            "descuento" => $descuento
        ));
        db_query($query);
    }
    // RELACIONAR PRESUPUESTO CON EL PROYECTO
    $query = make_update_query("tbl_presupuestos", array(
        "id_proyecto" => $id_proyecto
    ), "id='{$id_presupuesto}'");
    db_query($query);
    // VOLVER
    session_alert(LANG("projectcreatedok", "presupuestos"));
    javascript_history(-1);
    die();
}

if ($page == "posiblescli") {
    // BUSCAR DATOS DEL POSIBLE CLIENTE
    $id_posiblecli = abs(intval(getParam("id")));
    $query = "SELECT * FROM tbl_posiblescli WHERE id='$id_posiblecli'";
    $row = execute_query($query);
    if ($row === null) {
        action_denied();
    }
    // CREAR CLIENTE
    $id_campanya = $row["id_campanya"];
    $nombre = $row["nombre"];
    $cif = $row["cif"];
    $comentarios = $row["comentarios"];
    $direccion = $row["direccion"];
    $id_pais = $row["id_pais"];
    $id_provincia = $row["id_provincia"];
    $id_poblacion = $row["id_poblacion"];
    $id_codpostal = $row["id_codpostal"];
    $nombre_pais = $row["nombre_pais"];
    $nombre_provincia = $row["nombre_provincia"];
    $nombre_poblacion = $row["nombre_poblacion"];
    $nombre_codpostal = $row["nombre_codpostal"];
    $email = $row["email"];
    $web = $row["web"];
    $tel_fijo = $row["tel_fijo"];
    $tel_movil = $row["tel_movil"];
    $fax = $row["fax"];
    $query = make_insert_query("tbl_clientes", array(
        "id_campanya" => $id_campanya,
        "id_tipo" => 1,
        "nombre" => $nombre,
        "nombre1" => $nombre,
        "nombre2" => $nombre,
        "cif" => $cif,
        "comentarios" => $comentarios,
        "direccion" => $direccion,
        "id_pais" => $id_pais,
        "id_provincia" => $id_provincia,
        "id_poblacion" => $id_poblacion,
        "id_codpostal" => $id_codpostal,
        "nombre_pais" => $nombre_pais,
        "nombre_provincia" => $nombre_provincia,
        "nombre_poblacion" => $nombre_poblacion,
        "nombre_codpostal" => $nombre_codpostal,
        "email" => $email,
        "web" => $web,
        "tel_fijo" => $tel_fijo,
        "tel_movil" => $tel_movil,
        "fax" => $fax
    ));
    db_query($query);
    // OBTENER ID DEL NUEVO CLIENTE
    $query = "SELECT MAX(id) FROM tbl_clientes";
    $id_cliente = execute_query($query);
    // MOVER CONTROL DEL REGISTRO DE POSIBLES CLIENTES A CLIENTES
    $id_aplicacion = page2id("clientes");
    $id_aplicacion3 = page2id("posiblescli");
    $query = make_update_query("tbl_registros", array(
        "id_aplicacion" => $id_aplicacion,
        "id_registro" => $id_cliente
    ), "id_aplicacion='{$id_aplicacion3}' AND id_registro='{$id_posiblecli}'");
    db_query($query);
    // BORRAR EL POSIBLE CLIENTE
    $query = "DELETE FROM tbl_posiblescli WHERE id='{$id_posiblecli}'";
    db_query($query);
    // AÑADIR CONTROL DEL REGISTRO
    $id_aplicacion = page2id("clientes");
    $id_usuario = current_user();
    $datetime = current_datetime();
    make_control($id_aplicacion, $id_cliente);
    make_indexing($id_aplicacion, $id_cliente);
    // CREAR CONTACTO
    $contacto = $row["contacto"];
    $cargo = $row["cargo"];
    $query = make_insert_query("tbl_contactos", array(
        "id_registro" => $id_cliente,
        "id_aplicacion" => $id_aplicacion,
        "nombre" => $contacto,
        "nombre1" => $contacto,
        "nombre2" => $contacto,
        "cargo" => $cargo,
        "comentarios" => $comentarios,
        "direccion" => $direccion,
        "id_pais" => $id_pais,
        "id_provincia" => $id_provincia,
        "id_poblacion" => $id_poblacion,
        "id_codpostal" => $id_codpostal,
        "nombre_pais" => $nombre_pais,
        "nombre_provincia" => $nombre_provincia,
        "nombre_poblacion" => $nombre_poblacion,
        "nombre_codpostal" => $nombre_codpostal,
        "email" => $email,
        "web" => $web,
        "tel_fijo" => $tel_fijo,
        "tel_movil" => $tel_movil,
        "fax" => $fax
    ));
    db_query($query);
    // OBTENER ID DEL NUEVO CONTACTO
    $query = "SELECT MAX(id) FROM tbl_contactos";
    $id_contacto = execute_query($query);
    // AÑADIR CONTROL DEL REGISTRO
    $id_aplicacion2 = page2id("contactos");
    make_control($id_aplicacion2, $id_contacto);
    make_indexing($id_aplicacion2, $id_contacto);
    // RELACIONAR AGENDAS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
    $query = make_update_query("tbl_agenda", array(
        "id_posiblecli" => 0,
        "id_cliente" => $id_cliente
    ), "id_posiblecli='{$id_posiblecli}'");
    db_query($query);
    // RELACIONAR PRESUPUESTOS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
    $query = make_update_query("tbl_presupuestos", array(
        "id_posiblecli" => 0,
        "id_cliente" => $id_cliente
    ), "id_posiblecli='{$id_posiblecli}'");
    db_query($query);
    // RELACIONAR ACTAS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
    $query = make_update_query("tbl_actas", array(
        "id_posiblecli" => 0,
        "id_cliente" => $id_cliente
    ), "id_posiblecli='{$id_posiblecli}'");
    db_query($query);
    // RELACIONAR FICHEROS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
    $id_aplicacion3 = page2id("posiblescli");
    $query = make_update_query("tbl_ficheros", array(
        "id_aplicacion" => $id_aplicacion,
        "id_registro" => $id_cliente
    ), "id_aplicacion='{$id_aplicacion3}' AND id_registro='{$id_posiblecli}'");
    db_query($query);
    // RELACIONAR COMENTARIOS DEL POSIBLE CLIENTE CON EL NUEVO CLIENTE
    $query = make_update_query("tbl_comentarios", array(
        "id_aplicacion" => $id_aplicacion,
        "id_registro" => $id_cliente
    ), "id_aplicacion='{$id_aplicacion3}' AND id_registro='{$id_posiblecli}'");
    db_query($query);
    // VOLVER
    session_alert(LANG("clientcreatedok", "posiblescli"));
    javascript_history(-2);
    die();
}
