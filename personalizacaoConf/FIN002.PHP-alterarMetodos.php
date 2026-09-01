<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class FIN002 extends FIN002_ {
	// defina os métodos para sobreescrever

    /**
     * listaItensParaAgrupar
     *
     * @param bool $usarGroupBy
     * @param String $extra
     * @return String
     */    
    public function listaItensParaAgrupar($usarGroupBy = false, $extra)
    {
        global $g;
        $extra = base64_decode($extra);
        $extra = gPegarParteWhere(gInsertExtraWhere($extra, "T015_Data_Pagamento='0000-00-00' AND T015_Flag_Cancelada!='S' AND T015_Flag_Reparcelar='S' AND (T015_T015_Id_Agrupado<=0 or T015_T015_Id_Agrupado is null) AND T015_C004_Id='{$g['empresaAtual']}'"));
        $sql   = " SELECT ";
        $sql .= " * ";
        $sql .= " FROM T015 ";
        $sql .= " LEFT JOIN D024 ON D024_Id=T015_D024_Id ";
        $sql .= " LEFT JOIN D073 ON D073_Id=T015_D073_Id ";
        $sql .= " LEFT JOIN D031 ON D031_Id=T015_D031_Id ";
        $sql .= " LEFT JOIN D014 ON D014_Id=T015_D014_Id ";
        $sql .= " LEFT JOIN D032 ON D032_Id=D014_D032_Id ";
        $sql .= " LEFT JOIN D154 ON D154_Id=D014_D154_Id ";
        $sql .= " LEFT JOIN T007 ON T007_Id=T015_T007_Id ";
        $sql .= " LEFT JOIN C004 ON C004_Id=T015_C004_Id ";
        $sql .= " LEFT JOIN C007 ON C007_Id=T007_C007_Id_Vendedor_Interno ";
        $sql .= " WHERE {$extra}";
        //$sql .= " WHERE T015_C004_Id='{$g['empresaAtual']}'";
        //$sql .= "   AND T015_Data_Pagamento='0000-00-00'";
        //$sql .= "   AND T015_Flag_Reparcelar='S'";
        //$sql .= "   AND ifnull(T015_T035_Id,0)<=0";
        //$sql .= "   AND (T015_T015_Id_Agrupado<=0 or T015_T015_Id_Agrupado is null)";        
        if ($usarGroupBy) {
            $sql .= " GROUP BY SUBSTR(D024_CNPJ,1,10)";
        }
        
        $sqlReturn = mysql_query($sql);
        $resultado = array();
        while ($row = mysql_fetch_assoc($sqlReturn)) {
            $resultado[] = $row;
        }
        return $resultado;
    }

}
