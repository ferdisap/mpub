<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- <xsl:include href="levelledParaAlts.xsl"/> -->
  <!-- <xsl:include href="tilte.xsl"/> -->
  <!-- <xsl:include href="captionGroup.xsl"/> -->
  <!-- <xsl:include href="caption.xsl"/> -->
  <!-- <xsl:include href="crewDrill.xsl"/> -->
  <!-- <xsl:include href="warning.xsl"/> -->
  <!-- <xsl:include href="caution.xsl"/> -->
  <!-- <xsl:include href="note.xsl"/> -->
  <!-- <xsl:include href="circuitBreakerDescrGroup.xsl"/> -->
  <!-- <xsl:include href="para.xsl"/> -->
  <!-- <xsl:include href="figure.xsl"/> -->
  <!-- <xsl:include href="figureAlts.xsl"/> -->
  <!-- <xsl:include href="multimedia.xsl"/> -->
  <!-- <xsl:include href="multimediaAlts.xsl"/> -->
  <!-- <xsl:include href="foldout.xsl"/> -->
  <!-- <xsl:include href="table.xsl"/> -->
  
  <!-- <xsl:include href="custom_getPosition.xsl"/> -->
  <!-- BERHASIL, cek di demo5 04. -->
  
  <xsl:include href="attribute/id.xsl" />
  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:include href="helper/position.xsl"/>
  <xsl:include href="./group/textElemGroup.xsl" />
  <xsl:include href="./group/listElemGroup.xsl" />
  <xsl:include href="./element/levelledPara.xsl"/>
  <xsl:include href="./element/warningcautionnote.xsl"/>
  
  <xsl:param name="padding_levelPara_1"/>
  <xsl:param name="padding_levelPara_2"/>
  <xsl:param name="padding_levelPara_3"/>
  <xsl:param name="padding_levelPara_4"/>
  <xsl:param name="padding_levelPara_5"/>

  <xsl:param name="fontsize_levelledPara_title_1"/>
  <xsl:param name="fontsize_levelledPara_title_2"/>
  <xsl:param name="fontsize_levelledPara_title_3"/>
  <xsl:param name="fontsize_levelledPara_title_4"/>
  <xsl:param name="fontsize_levelledPara_title_5"/>

  <xsl:param name="fontsize_figure_title"/>

  <xsl:param name="dmOwner"/>
  <xsl:param name="absolute_asset_path"/>
  
  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template match="dmodule">
    <xsl:apply-templates select="//content/description"/>
  </xsl:template>
  
  <xsl:template match="description">
  <!-- jangan dibungkus div karena div akan di render, dan akan menambah page kosong di akhir
    <div>  </div> -->
    <xsl:apply-templates select="levelledPara"/>
  </xsl:template>

</xsl:stylesheet>