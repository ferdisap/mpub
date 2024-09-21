<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">
  
  <xsl:template match="attentionSequentialList">
    <fo:block text-align="left">
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <fo:block><xsl:apply-templates select="title|__cgmark"/></fo:block>
      <fo:list-block provisional-distance-between-starts="0.7cm" provisional-label-separation="0.15cm">
        <xsl:apply-templates select="attentionSequentialListItem|__cgmark"/>
      </fo:list-block>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>    
  </xsl:template>
  
  <xsl:template match="attentionRandomList">
    <fo:block text-align="left">
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <fo:block><xsl:apply-templates select="title|__cgmark"/></fo:block>
      <fo:list-block provisional-distance-between-starts="0.5cm" provisional-label-separation="0.5cm">
        <xsl:apply-templates select="attentionRandomListItem|__cgmark">
          <xsl:with-param name="listItemPrefix" select="string(@listItemPrefix)"/>
        </xsl:apply-templates>
      </fo:list-block>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="attentionSequentialListItem">
    <fo:list-item>
      <fo:list-item-label end-indent="label-end()">
        <fo:block>
          <xsl:number/><xsl:text>.</xsl:text>
        </fo:block>
      </fo:list-item-label>
      <fo:list-item-body start-indent="body-start()">
        <xsl:call-template name="cgmark_begin"/>
        <xsl:call-template name="add_applicability"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:call-template name="add_security"/>
        <xsl:apply-templates/>
        <xsl:call-template name="cgmark_end"/>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>

  <xsl:template match="attentionRandomListItem">
    <xsl:param name="listItemPrefix">pf02</xsl:param>
    <fo:list-item>
      <xsl:call-template name="style-listItem"/>
      <fo:list-item-label end-indent="label-end()">
        <fo:block>
          <xsl:choose>
            <xsl:when test="$listItemPrefix = 'pf01'">
              <xsl:text>&#160;&#160;&#160;</xsl:text>
            </xsl:when>
            <xsl:when test="$listItemPrefix = 'pf03' and (parent::attentionRandomList/parent::note or parent::attentionRandomList/parent::notePara)">
              <xsl:text>-</xsl:text>
            </xsl:when>
            <xsl:when test="$listItemPrefix = 'pf07' and (parent::warning or parent::caution or parent::warningAndCautionPara)">
              <xsl:text>&#x2022;</xsl:text>
            </xsl:when>
            <xsl:when test="$listItemPrefix = 'pf02'">
              <xsl:choose>
                <xsl:when test="count(ancestor::attentionRandomListItem) mod 2"><xsl:text>-</xsl:text></xsl:when>
                <xsl:otherwise><xsl:text>&#x2022;</xsl:text></xsl:otherwise>
              </xsl:choose>
            </xsl:when>
            <xsl:otherwise><fo:leader/></xsl:otherwise>
          </xsl:choose>
        </fo:block>
      </fo:list-item-label>
      <fo:list-item-body start-indent="body-start()">
        <xsl:call-template name="cgmark_begin"/>
        <xsl:call-template name="add_applicability"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:call-template name="add_security"/>
        <xsl:apply-templates/>
        <xsl:call-template name="cgmark_end"/>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>

  <xsl:template match="attentionListItemPara">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block>
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>


</xsl:transform>