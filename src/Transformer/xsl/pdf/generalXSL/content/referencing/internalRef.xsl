<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
    1. belum mengutilize internalRef@referredFramgent
    2. belum mengutilize internalRef@targetTitle
    3. belum mengutilize irtt03 untuk multimedia
    4. belum mengutilize irtt10 untuk multimedia object
    5. untuk irtt08, step baru mencakup elemen crewDrillStep (belum: untuk procedures, checklist, etc)
   -->

  <xsl:template match="internalRef" name="add_internalRef">
    <xsl:param name="irtt" select="string(@internalRefTargetType)"/>
    <xsl:call-template name="cgmark_begin"/>
    <fo:inline color="{$colorLink}" text-decoration="underline">
      <xsl:call-template name="add_referredFragment"/>
      <xsl:call-template name="add_inline_applicability"/>
      <xsl:call-template name="add_inline_controlAuthority"/>
      <xsl:call-template name="add_inline_security"/>
      <fo:basic-link internal-destination="{string(@internalRefId)}">
        <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','internalRefTargetType', string($irtt), 'abbreviation', 'extension' ,'default')" />
        <xsl:text>&#160;</xsl:text>
        <xsl:choose>
          <xsl:when test="string(.) != ''">
            <xsl:apply-templates/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="irtt">
              <xsl:with-param name="irtt" select="$irtt"/>
            </xsl:call-template>
          </xsl:otherwise>
        </xsl:choose>
      </fo:basic-link>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template name="irtt">
    <xsl:param name="irtt" select="string(@internalRefTargetType)"/>
    <xsl:param name="id" select="string(@internalRefId)"/>
    <xsl:choose>
      <xsl:when test="$irtt = 'irtt01'">
        <xsl:for-each select="//figure[string(@id) = $id]">
          <xsl:number level="any"/>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt02'">
        <xsl:for-each select="//table[string(@id) = $id]">
          <xsl:number level="any"/>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt03'">
        <!-- TBD, karena elemen multimedia nya belum dibuat XSL nya. -->
      </xsl:when>
      <xsl:when test="$irtt = 'irtt04'">
        <xsl:apply-templates select="//supplyDesc[string(@id) = $id]/name|//supplyDesc[string(@id) = $id]/shortName"/>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt05'">
        <xsl:apply-templates select="//supportEquipDescr[string(@id) = $id]/name|//supportEquipDescr[string(@id) = $id]/shortName"/>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt06'">
        <xsl:apply-templates select="//spareDescr[string(@id) = $id]/name|//spareDescr[string(@id) = $id]/shortName"/>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt07'">
        <xsl:for-each select="//levelledPara[string(@id) = $id]">
          <xsl:number level="multiple" count="levelledPara"/>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt08'">
        <xsl:variable name="targetElement" select="//*[string(@id) = $id]"/>
        <xsl:choose>
          <xsl:when test="name($targetElement) = 'crewDrillStep'">
            <xsl:for-each select="$targetElement">
              <xsl:call-template name="crewDrillStepNumber"/>
            </xsl:for-each>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt09'">
        <xsl:variable name="targetElement" select="//graphic[string(@id) = $id]"/>
        <xsl:for-each select="$targetElement">
          <xsl:call-template name="captionGraphic">
            <xsl:with-param name="onlyPrefix" select="'yes'"/>
          </xsl:call-template>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt10'">
        <!-- TBD, karena elemen multimedia object-nya belum dibuat XSL nya. -->
      </xsl:when>
      <xsl:when test="$irtt = 'irtt13'">
        <xsl:apply-templates select="//zoneRef[string(@id) = $id]/name | //zoneRef[string(@id) = $id]/shortName"/>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt14'">
        <xsl:apply-templates select="//workLocation[string(@id) = $id]/workArea"/>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt15'">
        <xsl:choose>
          <xsl:when test="boolean(//sbmaterialSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbmaterialSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbSupportEquipSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbSupportEquipSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualSupportEquip[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbIndividualSupportEquip[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbExternalSupportEquipSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbExternalSupportEquipSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbSupplySet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbSupplySet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualSupply[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbIndividualSupply[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbExternalSupplySet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbExternalSupplySet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbSpareSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbSpareSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualSpare[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbIndividualSpare[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbExternalSpareSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbExternalSpareSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbRemovedSpareSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbRemovedSpareSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualRemovedSpareSet[string(@id) = $id]/shortName)">
            <xsl:apply-templates select="//sbIndividualRemovedSpareSet[string(@id) = $id]/shortName"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$irtt = 'irtt16'">
        <xsl:apply-templates select="//accessPointRef[string(@id) = $id]/name"/>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  
</xsl:transform>