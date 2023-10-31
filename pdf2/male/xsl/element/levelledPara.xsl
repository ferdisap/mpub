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

  <xsl:include href="para.xsl"/>
  <xsl:include href="title.xsl"/>
  <xsl:include href="figure.xsl"/>
  <xsl:include href="table.xsl"/>

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template match="levelledPara">
    <!-- <div> -->
    <div>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>

      <xsl:variable name="numberedPar">
        <xsl:call-template name="checkParent"/>
        <xsl:number/>
      </xsl:variable>
      <xsl:variable name="level">
        <xsl:value-of select="string-length(translate($numberedPar, '.', ''))"/>
      </xsl:variable>
      
      <!-- set the padding left for different level levelledPara -->
      <!-- <xsl:attribute name="style">
        <xsl:choose>
          <xsl:when test="$level = '1'">
            <xsl:text>padding-left:30pt</xsl:text>
          </xsl:when>
          <xsl:when test="$level = '2'">
            <xsl:text>padding-left:50pt</xsl:text>
          </xsl:when>
          <xsl:when test="$level = '3'">
            <xsl:text>padding-left:70pt</xsl:text>
          </xsl:when>
          <xsl:when test="$level = '4'">
            <xsl:value-of select="$padding_levelPara_4"/>
          </xsl:when>
          <xsl:when test="$level = '5'">
            <xsl:value-of select="$padding_levelPara_5"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="'0'"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute> -->
      <xsl:attribute name="paddingleft">
        <xsl:choose>
          <xsl:when test="$level = '1'">
            <xsl:value-of select="$padding_levelPara_1"/>
          </xsl:when>
          <xsl:when test="$level = '2'">
            <xsl:value-of select="$padding_levelPara_2"/>
          </xsl:when>
          <xsl:when test="$level = '3'">
            <xsl:value-of select="$padding_levelPara_3"/>
          </xsl:when>
          <xsl:when test="$level = '4'">
            <xsl:value-of select="$padding_levelPara_4"/>
          </xsl:when>
          <xsl:when test="$level = '5'">
            <xsl:value-of select="$padding_levelPara_5"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="'0'"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>

      <xsl:if test="$level = '1'">
        <xsl:attribute name="addIntentionallyLeftBlank">true</xsl:attribute>
      </xsl:if>

      <xsl:apply-templates/>
    </div>
  </xsl:template>


</xsl:stylesheet>